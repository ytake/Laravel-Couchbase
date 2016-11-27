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

namespace Ytake\LaravelCouchbase\Query;

use CouchbaseBucket;
use CouchbaseViewQuery;
use Illuminate\Contracts\Events\Dispatcher;
use Ytake\LaravelCouchbase\Events\ViewQuerying;

/**
 * Class View.
 *
 * @see    http://developer.couchbase.com/documentation/server/4.1/developer-guide/views-intro.html
 * @codeCoverageIgnore
 * @author Yuuki Takezawa<yuuki.takezawa@comnect.jp.net>
 */
class View
{
    /** @var CouchbaseBucket */
    protected $bucket;

    /** @var Dispatcher */
    protected $dispatcher;

    /**
     * View constructor.
     *
     * @param CouchbaseBucket $bucket
     * @param Dispatcher|null $dispatcher
     */
    public function __construct(CouchbaseBucket $bucket, Dispatcher $dispatcher = null)
    {
        $this->bucket = $bucket;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param $designDoc
     * @param $name
     *
     * @return \_CouchbaseDefaultViewQuery
     */
    public function from($designDoc, $name)
    {
        return CouchbaseViewQuery::from($designDoc, $name);
    }

    /**
     * @param $designDoc
     * @param $name
     *
     * @return \_CouchbaseSpatialViewQuery
     */
    public function fromSpatial($designDoc, $name)
    {
        return CouchbaseViewQuery::fromSpatial($designDoc, $name);
    }

    /**
     * @param CouchbaseViewQuery $viewQuery
     * @param bool               $jsonAsArray
     *
     * @return mixed
     */
    public function execute(CouchbaseViewQuery $viewQuery, $jsonAsArray = false)
    {
        if (isset($this->dispatcher)) {
            $this->dispatcher->fire(new ViewQuerying($viewQuery));
        }

        return $this->bucket->query($viewQuery, $jsonAsArray);
    }
}
