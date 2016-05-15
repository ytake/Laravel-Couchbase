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

/**
 * Class ViewQuery.
 */
class ViewQuery
{
    /** @var \CouchbaseBucket  */
    protected $bucket;

    /**
     * ViewQuery constructor.
     *
     * @param \CouchbaseBucket $bucket
     */
    public function __construct(\CouchbaseBucket $bucket)
    {
        $this->bucket = $bucket;
    }

    /**
     * @param $designDoc
     * @param $name
     *
     * @return \_CouchbaseDefaultViewQuery
     */
    public function from($designDoc, $name)
    {
        return \CouchbaseViewQuery::from($designDoc, $name);
    }
}
