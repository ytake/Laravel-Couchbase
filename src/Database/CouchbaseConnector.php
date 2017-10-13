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

namespace Ytake\LaravelCouchbase\Database;

use Couchbase\Cluster;
use Couchbase\PasswordAuthenticator;

/**
 * Class CouchbaseConnector.
 *
 * @author Yuuki Takezawa<yuuki.takezawa@comnect.jp.net>
 */
class CouchbaseConnector implements Connectable
{
    /** @var string[] */
    protected $configure = [
        'host'     => 'couchbase://127.0.0.1',
        'user'     => '',
        'password' => '',
    ];

    /**
     * @param array $servers
     *
     * @return Cluster
     */
    public function connect(array $servers): Cluster
    {
        $configure = array_merge($this->configure, $servers);
        $cluster = new Cluster($configure['host']);
        if (!empty($configure['user']) && !empty($configure['password'])) {
            $authenticator = new PasswordAuthenticator();
            $authenticator->username(strval($configure['user']))
                ->password(strval($configure['password']));
            $cluster->authenticate($authenticator);
        }

        return $cluster;
    }
}
