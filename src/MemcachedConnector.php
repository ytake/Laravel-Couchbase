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

namespace Ytake\LaravelCouchbase;

/**
 * Class MemcachedConnector.
 * for couchbase memcached bucket.
 *
 * @codeCoverageIgnore
 *
 * @author Yuuki Takezawa<yuuki.takezawa@comnect.jp.net>
 */
class MemcachedConnector extends \Illuminate\Cache\MemcachedConnector
{
    /**
     * Create a new Memcached connection.
     *
     * @param  array       $servers
     * @param  string|null $connectionId
     * @param  array       $options
     * @param  array       $credentials
     *
     * @return \Memcached
     *
     * @throws \RuntimeException
     */
    public function connect(
        array $servers,
        $connectionId = null,
        array $options = [],
        array $credentials = []
    ) {
        $memcached = $this->getMemcached($connectionId, [], []);

        foreach ($servers as $server) {
            $memcached->addServer(
                $server['host'], $server['port'], $server['weight']
            );
        }

        return $memcached;
    }

    /**
     * {@inheritdoc}
     */
    protected function getMemcached($connectionId, array $credentials, array $options)
    {
        return $this->createMemcachedInstance($connectionId);
    }
}
