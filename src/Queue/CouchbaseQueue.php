<?php
declare(strict_types=1);

/**
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Ytake\LaravelCouchbase\Queue;

use Illuminate\Queue\DatabaseQueue;
use Illuminate\Database\Query\Builder;
use Illuminate\Queue\Jobs\DatabaseJob;
use Illuminate\Queue\Jobs\DatabaseJobRecord;
use Ytake\LaravelCouchbase\Database\CouchbaseConnection;

/**
 * Class CouchbaseQueue
 *
 * @author Yuuki Takezawa<yuuki.takezawa@comnect.jp.net>
 */
class CouchbaseQueue extends DatabaseQueue
{
    /**
     * The couchbase bucket that holds the jobs.
     *
     * @var string
     */
    protected $table;

    /** @var CouchbaseConnection */
    protected $database;

    /**
     * {@inheritdoc}
     */
    public function pop($queue = null)
    {
        $queue = $this->getQueue($queue);
        if ($job = $this->getNextAvailableJob($queue)) {
            return $this->marshalJob($queue, $job);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function marshalJob($queue, $job)
    {
        $job = $this->markJobAsReserved($job);

        return new DatabaseJob(
            $this->container, $this, $job, $this->connectionName, $queue
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getNextAvailableJob($queue)
    {
        $job = $this->database->table($this->table)
            ->where('queue', $this->getQueue($queue))
            ->where(function (Builder $query) {
                $this->isAvailable($query);
                $this->isReservedButExpired($query);
            })
            ->orderBy('id', 'asc')
            ->first(['*', 'meta().id']);

        return $job ? new DatabaseJobRecord((object)$job) : null;
    }

    /**
     * {@inheritdoc}
     */
    protected function markJobAsReserved($job)
    {
        $bucket = $this->table;
        /** @var \Couchbase\Bucket $openBucket */
        $openBucket = $this->database->openBucket($bucket);
        // lock bucket
        $meta = $openBucket->getAndLock($job->id, 10);
        $meta->value->attempts = $job->$bucket->attempts + 1;
        $meta->value->reserved_at = $job->touch();
        $openBucket->replace($job->id, $meta->value, ['cas' => $meta->cas]);

        return $meta->value;
    }

    /**
     * {@inheritdoc}
     */
    public function bulk($jobs, $data = '', $queue = null)
    {
        foreach ((array)$jobs as $job) {
            $this->push($job, $data, $queue);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteReserved($queue, $id)
    {
        $this->database->table($this->table)->where('id', $id)->delete();
    }

    /**
     * {@inheritdoc}
     */
    protected function pushToDatabase($queue, $payload, $delay = 0, $attempts = 0)
    {
        $attributes = $this->buildDatabaseRecord(
            $this->getQueue($queue), $payload, $this->availableAt($delay), $attempts
        );
        $increment = $this->incrementKey();
        $attributes['id'] = $increment;
        $result = $this->database->table($this->table)
            ->key($this->uniqueKey($attributes))->insert($attributes);
        if ($result) {
            return $increment;
        }

        return false;
    }

    /**
     * generate increment key
     *
     * @param int $initial
     *
     * @return int
     */
    protected function incrementKey($initial = 1)
    {
        $result = $this->database->openBucket($this->table)
            ->counter($this->identifier(), $initial, ['initial' => abs($initial)]);

        return $result->value;
    }

    /**
     * @param array $attributes
     *
     * @return string
     */
    protected function uniqueKey(array $attributes): string
    {
        $array = array_only($attributes, ['queue', 'attempts', 'id']);

        return implode(':', $array);
    }

    /**
     * @return string
     */
    protected function identifier(): string
    {
        return __CLASS__ . ':sequence';
    }
}
