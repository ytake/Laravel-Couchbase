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

namespace Ytake\LaravelCouchbase\Cache;

use Couchbase\Bucket;
use Couchbase\Cluster;
use Couchbase\Exception as CouchbaseException;
use Illuminate\Cache\RetrievesMultipleKeys;
use Illuminate\Cache\TaggableStore;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Contracts\Cache\Store;
use Ytake\LaravelCouchbase\Exceptions\FlushException;

/**
 * Class CouchbaseStore.
 *
 * @author Yuuki Takezawa<yuuki.takezawa@comnect.jp.net>
 */
class CouchbaseStore extends TaggableStore implements Store
{
    use RetrievesMultipleKeys;

    /** @var string */
    protected $prefix;

    /** @var Bucket */
    protected $bucket;

    /** @var Cluster */
    protected $cluster;

    /**
     * CouchbaseStore constructor.
     *
     * @param Cluster     $cluster
     * @param string      $bucket
     * @param string      $password
     * @param string|null $prefix
     * @param string      $serialize
     */
    public function __construct(
        Cluster $cluster,
        string $bucket,
        string $password = '',
        string $prefix = null,
        string $serialize = 'php'
    ) {
        $this->cluster = $cluster;
        $this->setBucket($bucket, $password, $serialize);
        $this->setPrefix($prefix);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        try {
            $result = $this->bucket->get($this->resolveKey($key));

            return $this->getMetaDoc($result);
        } catch (CouchbaseException $e) {
            return;
        }
    }

    /**
     * Store an item in the cache if the key doesn't exist.
     *
     * @param string|array $key
     * @param mixed        $value
     * @param int          $minutes
     *
     * @return bool
     */
    public function add($key, $value, $minutes = 0): bool
    {
        $options = ($minutes === 0) ? [] : ['expiry' => ($minutes * 60)];
        try {
            $this->bucket->insert($this->resolveKey($key), $value, $options);

            return true;
        } catch (CouchbaseException $e) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function put($key, $value, $minutes)
    {
        $this->bucket->upsert($this->resolveKey($key), $value, ['expiry' => $minutes * 60]);
    }

    /**
     * {@inheritdoc}
     */
    public function increment($key, $value = 1)
    {
        return $this->bucket
            ->counter($this->resolveKey($key), $value, ['initial' => abs($value)])->value;
    }

    /**
     * {@inheritdoc}
     */
    public function decrement($key, $value = 1)
    {
        return $this->bucket
            ->counter($this->resolveKey($key), (0 - abs($value)), ['initial' => (0 - abs($value))])->value;
    }

    /**
     * {@inheritdoc}
     */
    public function forever($key, $value)
    {
        try {
            $this->bucket->insert($this->resolveKey($key), $value);
        } catch (CouchbaseException $e) {
            // bucket->insert when called from resetTag in TagSet can throw CAS exceptions, ignore.\
            $this->bucket->upsert($this->resolveKey($key), $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function forget($key)
    {
        try {
            $this->bucket->remove($this->resolveKey($key));
        } catch (\Exception $e) {
            // Ignore exceptions from remove
        }
    }

    /**
     * flush bucket.
     *
     * @throws FlushException
     * @codeCoverageIgnore
     */
    public function flush()
    {
        $result = $this->bucket->manager()->flush();
        if (isset($result['_'])) {
            throw new FlushException($result);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Set the cache key prefix.
     *
     * @param string $prefix
     */
    public function setPrefix(string $prefix)
    {
        $this->prefix = !empty($prefix) ? $prefix . ':' : '';
    }

    /**
     * @param string $bucket
     * @param string $password
     * @param string $serialize
     *
     * @return CouchbaseStore
     */
    public function setBucket(string $bucket, string $password = '', string $serialize = 'php'): CouchbaseStore
    {
        $this->bucket = $this->cluster->openBucket($bucket, $password);
        if ($serialize === 'php') {
            $this->bucket->setTranscoder('couchbase_php_serialize_encoder', 'couchbase_default_decoder');
        }

        return $this;
    }

    /**
     * @param $keys
     *
     * @return array|string
     */
    private function resolveKey($keys)
    {
        if (is_array($keys)) {
            $result = [];
            foreach ($keys as $key) {
                $result[] = $this->prefix . $key;
            }

            return $result;
        }

        return $this->prefix . $keys;
    }

    /**
     * @param $meta
     *
     * @return array|null
     */
    protected function getMetaDoc($meta)
    {
        if ($meta instanceof \Couchbase\Document) {
            return $meta->value;
        }
        if (is_array($meta)) {
            $result = [];
            foreach ($meta as $row) {
                $result[] = $this->getMetaDoc($row);
            }

            return $result;
        }

        return null;
    }

    /**
     * Get a lock instance.
     *
     * @param  string  $name
     * @param  int  $seconds
     * @return \Illuminate\Contracts\Cache\Lock
     */
    public function lock(string $name, int $seconds = 0): Lock
    {
        return new CouchbaseLock($this->bucket, $this->prefix.$name, $seconds);
    }
}
