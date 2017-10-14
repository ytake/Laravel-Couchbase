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
use Couchbase\Document;
use Couchbase\Exception as CouchbaseException;
use Illuminate\Cache\Lock;
use Illuminate\Contracts\Cache\Lock as Lockable;

/**
 * Class CouchbaseLock
 *
 * @author Yuuki Takezawa<yuuki.takezawa@comnect.jp.net>
 */
class CouchbaseLock extends Lock implements Lockable
{
    /** @var Bucket */
    protected $bucket;

    /**
     * CouchbaseLock constructor.
     *
     * @param Bucket $bucket
     * @param string $name
     * @param int    $seconds
     */
    public function __construct(Bucket $bucket, string $name, int $seconds)
    {
        parent::__construct($name, $seconds);

        $this->bucket = $bucket;
    }

    /**
     * @return bool
     */
    public function acquire()
    {
        try {
            $result = $this->bucket->insert($this->name, 1, ['expiry' => $this->seconds]);
            if ($result instanceof Document) {
                return true;
            }
        } catch (CouchbaseException $e) {
            return false;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function release()
    {
        $this->bucket->remove($this->name);
    }
}
