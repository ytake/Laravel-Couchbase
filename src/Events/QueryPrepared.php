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

namespace Ytake\LaravelCouchbase\Events;

/**
 * Class QueryPrepared
 *
 * @author Yuuki Takezawa<yuuki.takezawa@comnect.jp.net>
 */
class QueryPrepared
{
    /** @var array */
    public $object = [];

    /**
     * QueryPrepared constructor.
     *
     * @param $queryObject
     */
    public function __construct($queryObject)
    {
        /** @var \CouchbaseN1qlQuery $queryObject */
        if ($this->isN1ql($queryObject)) {
            $this->object = $queryObject->toObject();
        }
    }

    /**
     * @param mixed $queryObject
     *
     * @return bool
     */
    protected function isN1ql($queryObject)
    {
        if ($queryObject instanceof \CouchbaseN1qlQuery) {
            return true;
        }

        return false;
    }
}
