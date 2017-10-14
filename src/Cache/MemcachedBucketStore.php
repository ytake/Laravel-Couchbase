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

use Illuminate\Cache\MemcachedStore;

/**
 * Class MemcachedBucketStore.
 *
 * @author Yuuki Takezawa<yuuki.takezawa@comnect.jp.net>
 * @deprecated
 */
class MemcachedBucketStore extends MemcachedStore
{
    /** @var  string[] */
    protected $servers;

    /** @var array */
    protected $options = [
        CURLOPT_RETURNTRANSFER => false,
        CURLOPT_SSL_VERIFYPEER => false,
    ];

    /** @var int */
    protected $port = 8091;

    /** @var int */
    protected $timeout = 1;

    /** @var string */
    protected $flushEndpoint = ':%s/pools/default/buckets/%s/controller/doFlush';

    /**
     * MemcachedBucketStore constructor.
     *
     * @param \Memcached $memcached
     * @param string     $prefix
     * @param array      $servers
     */
    public function __construct(\Memcached $memcached, string $prefix = '', array $servers)
    {
        parent::__construct($memcached, $prefix);
        $this->servers = $servers;
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return int|bool
     */
    public function increment($key, $value = 1)
    {
        if ($integer = $this->get($key)) {
            $this->put($key, $integer + $value, 0);

            return $integer + $value;
        }

        $this->put($key, $value, 0);

        return $value;
    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return int|bool
     */
    public function decrement($key, $value = 1)
    {
        $decrement = 0;
        if ($integer = $this->get($key)) {
            $decrement = $integer - $value;
            if ($decrement <= 0) {
                $decrement = 0;
            }
            $this->put($key, $decrement, 0);

            return $decrement;
        }

        $this->put($key, $decrement, 0);

        return $decrement;
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $handler = curl_multi_init();
        foreach ($this->servers as $server) {
            $initialize = curl_init();
            $configureOption = (isset($server['options'])) ? $server['options'] : [];

            $options = array_replace($this->options, [
                CURLOPT_POST => true,
                CURLOPT_URL  => $server['host'] . sprintf($this->flushEndpoint, $this->port, $server['bucket']),
            ], $configureOption);
            curl_setopt_array($initialize, $options);
            curl_multi_add_handle($handler, $initialize);
        }

        $this->callMulti($handler);
    }

    /**
     * @param $handler
     *
     * @throws \RuntimeException
     */
    protected function callMulti($handler)
    {
        $running = null;

        do {
            $stat = curl_multi_exec($handler, $running);
        } while ($stat === CURLM_CALL_MULTI_PERFORM);
        if (!$running || $stat !== CURLM_OK) {
            throw new \RuntimeException('failed to initialized cURL');
        }

        do {
            curl_multi_select($handler, $this->timeout);
            do {
                $stat = curl_multi_exec($handler, $running);
            } while ($stat === CURLM_CALL_MULTI_PERFORM);
            do {
                if ($read = curl_multi_info_read($handler, $remains)) {
                    $response = curl_multi_getcontent($read['handle']);

                    if ($response === false) {
                        $info = curl_getinfo($read['handle']);
                        throw new \RuntimeException("error: {$info['url']}: {$info['http_code']}");
                    }
                    curl_multi_remove_handle($handler, $read['handle']);
                    curl_close($read['handle']);
                }
            } while ($remains);
        } while ($running);
        curl_multi_close($handler);
    }

    /**
     * @param int $second
     */
    public function timeout(int $second)
    {
        $this->timeout = $second;
    }

    /**
     * @param int $port
     */
    public function port(int $port)
    {
        $this->port = $port;
    }
}
