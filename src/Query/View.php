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

namespace Ytake\LaravelCouchbase\Query;

use Couchbase\Bucket;
use Couchbase\SpatialViewQuery;
use Couchbase\ViewQuery;
use Illuminate\Contracts\Events\Dispatcher;
use Ytake\LaravelCouchbase\Events\ViewQuerying;

/**
 * Class View.
 *
 * @see    http://developer.couchbase.com/documentation/server/4.6/developer-guide/views-intro.html
 *
 * @author Yuuki Takezawa<yuuki.takezawa@comnect.jp.net>
 */
class View
{
    /** @var Bucket */
    protected $bucket;

    /** @var Dispatcher */
    protected $dispatcher;

    /**
     * Specifies the mode of updating to perorm before and after executing the query
     *
     * @see \Couchbase\ViewQuery::UPDATE_BEFORE
     * @see \Couchbase\ViewQuery::UPDATE_NONE
     * @see \Couchbase\ViewQuery::UPDATE_AFTER
     */
    private $consistency = null;

    /**
     * View constructor.
     *
     * @param Bucket          $bucket
     * @param Dispatcher|null $dispatcher
     */
    public function __construct(Bucket $bucket, Dispatcher $dispatcher = null)
    {
        $this->bucket = $bucket;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param string $designDoc
     * @param string $name
     *
     * @return ViewQuery
     */
    public function from(string $designDoc, string $name): ViewQuery
    {
        return ViewQuery::from($designDoc, $name);
    }

    /**
     * @param string $designDoc
     * @param string $name
     *
     * @return SpatialViewQuery
     */
    public function fromSpatial(string $designDoc, string $name): SpatialViewQuery
    {
        return ViewQuery::fromSpatial($designDoc, $name);
    }

    /**
     * @param ViewQuery $viewQuery
     * @param bool      $jsonAsArray
     *
     * @return mixed
     */
    public function execute(ViewQuery $viewQuery, bool $jsonAsArray = false)
    {
        if (isset($this->dispatcher)) {
            $this->dispatcher->dispatch(new ViewQuerying($viewQuery));
        }
        if (!is_null($this->consistency)) {
            $viewQuery = $viewQuery->consistency($this->consistency);
        }

        return $this->bucket->query($viewQuery, $jsonAsArray);
    }

    /**
     * @param int $consistency
     */
    public function consistency(int $consistency)
    {
        $this->consistency = $consistency;
    }
}
