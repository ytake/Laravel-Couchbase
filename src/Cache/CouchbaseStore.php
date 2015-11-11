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

namespace Ytake\LaravelCouchbase\Cache;

use CouchbaseBucket;
use CouchbaseCluster;
use CouchbaseException;
use Illuminate\Cache\TaggableStore;
use Illuminate\Contracts\Cache\Store;
use Ytake\LaravelCouchbase\Exceptions\FlushException;

/**
 * Class CouchbaseStore
 */
class CouchbaseStore extends TaggableStore implements Store
{
    /** @var string */
    protected $prefix;

    /** @var CouchbaseBucket */
    protected $bucket;

    /** @var CouchbaseCluster */
    protected $cluster;

    /**
     * @param CouchbaseCluster $cluster
     * @param                  $bucket
     * @param null             $prefix
     */
    public function __construct(CouchbaseCluster $cluster, $bucket, $prefix = null)
    {
        $this->cluster = $cluster;
        $this->setBucket($bucket);
        $this->setPrefix($prefix);
    }

    /**
     * {@inheritDoc}
     */
    public function get($key)
    {
        try {
            $result = $this->bucket->get($this->resolveKey($key));

            return $this->getMetaDoc($result);
        } catch (CouchbaseException $e) {
            return null;
        }
    }

    /**
     * Store an item in the cache if the key doesn't exist.
     *
     * @param  string|array $key
     * @param  mixed        $value
     * @param  int          $minutes
     *
     * @return bool
     */
    public function add($key, $value, $minutes = 0)
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
        $this->bucket->insert($this->resolveKey($key), $value);
    }

    /**
     * {@inheritdoc}
     */
    public function forget($key)
    {
        $this->bucket->remove($this->resolveKey($key));
    }

    /**
     * flush bucket
     *
     * @throws FlushException
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
     * @param  string $prefix
     *
     * @return void
     */
    public function setPrefix($prefix)
    {
        $this->prefix = !empty($prefix) ? $prefix . ':' : '';
    }

    /**
     * @param        $bucket
     * @param string $password
     *
     * @return $this
     */
    public function setBucket($bucket, $password = '')
    {
        $this->bucket = $this->cluster->openBucket($bucket, $password);

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
        if ($meta instanceof \CouchbaseMetaDoc) {
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
}
