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
 * Class MemcachedConnector
 * for bucket type memcached
 */
class MemcachedConnector extends \Illuminate\Cache\MemcachedConnector
{
    /**
     * {@inheritdoc}
     */
    public function connect(array $servers)
    {
        $memcached = $this->getMemcached();

        foreach ($servers as $server) {
            $memcached->addServer(
                $server['host'], $server['port'], $server['weight']
            );
        }

        if ($memcached->getVersion() === false) {
            throw new \RuntimeException("Could not establish Memcached connection.");
        }

        return $memcached;
    }
}
