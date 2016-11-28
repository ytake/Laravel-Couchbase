<?php

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
use Illuminate\Queue\Jobs\DatabaseJob;
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
            $job = $this->markJobAsReserved($job);

            return new DatabaseJob(
                $this->container, $this, $job, $queue
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getNextAvailableJob($queue)
    {
        $job = $this->database->table($this->table)
            ->lockForUpdate()
            ->where('queue', $this->getQueue($queue))
            ->where(function ($query) {
                $this->isAvailable($query);
                $this->isReservedButExpired($query);
            })
            ->orderBy('id', 'asc')
            ->first(['*', 'meta().id']);

        return $job ? (object)$job : null;
    }

    /**
     * {@inheritdoc}
     */
    protected function markJobAsReserved($job)
    {
        $bucket = $this->table;
        /** @var \CouchbaseBucket $openBucket */
        $openBucket = $this->database->openBucket($bucket);
        // lock bucket
        $meta = $openBucket->getAndLock($job->id, 10);
        $meta->value->reserved = 1;
        $meta->value->reserved_at = $this->getTime();
        $meta->value->attempts = $job->value->attempts + 1;
        $openBucket->replace($job->id, $meta->value, ['cas' => $meta->cas]);

        return $meta->value;
    }

    /**
     * {@inheritdoc}
     */
    public function bulk($jobs, $data = '', $queue = null)
    {
        $queue = $this->getQueue($queue);

        $availableAt = $this->getAvailableAt(0);

        $records = array_map(function ($job) use ($queue, $data, $availableAt) {
            return $this->buildDatabaseRecord(
                $queue, $this->createPayload($job, $data), $availableAt
            );
        }, (array)$jobs);
        foreach ($records as $record) {
            $increment = $this->incrementKey();
            $record['id'] = $increment;
            $this->database->table($this->table)
                ->key($this->uniqueKey($record))->insert($record);
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
    protected function pushToDatabase($delay, $queue, $payload, $attempts = 0)
    {
        $attributes = $this->buildDatabaseRecord(
            $this->getQueue($queue), $payload, $this->getAvailableAt($delay), $attempts
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
    protected function uniqueKey(array $attributes)
    {
        $array = array_only($attributes, ['queue', 'attempts', 'id']);

        return implode(':', $array);
    }

    /**
     * @return string
     */
    protected function identifier()
    {
        return __CLASS__ . ':sequence';
    }
}
