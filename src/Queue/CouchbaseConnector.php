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

use Illuminate\Support\Arr;
use Illuminate\Queue\Connectors\ConnectorInterface;
use Illuminate\Database\ConnectionResolverInterface;
use Ytake\LaravelCouchbase\Database\CouchbaseConnection;

/**
 * Class CouchbaseConnector
 *
 * @author Yuuki Takezawa<yuuki.takezawa@comnect.jp.net>
 */
class CouchbaseConnector implements ConnectorInterface
{
    /** @var ConnectionResolverInterface */
    protected $connectionResolver;

    /**
     * CouchbaseConnector constructor.
     *
     * @param ConnectionResolverInterface $connectionResolver
     */
    public function __construct(ConnectionResolverInterface $connectionResolver)
    {
        $this->connectionResolver = $connectionResolver;
    }

    /**
     * @param array $config
     *
     * @return CouchbaseQueue|\Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        /** @var CouchbaseConnection $connection */
        $connection = $this->connectionResolver->connection($config['driver']);

        return new CouchbaseQueue(
            $connection,
            $config['bucket'],
            $config['queue'],
            Arr::get($config, 'retry_after', 60)
        );
    }
}
