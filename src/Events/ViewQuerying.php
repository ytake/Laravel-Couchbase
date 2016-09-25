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

use CouchbaseViewQuery;

/**
 * Class ViewQuerying
 *
 * @author Yuuki Takezawa<yuuki.takezawa@comnect.jp.net>
 */
class ViewQuerying
{
    /** @var mixed  Generates the view query as it will be passed to the server. */
    public $path;

    /**
     * ViewQuerying constructor.
     *
     * @param CouchbaseViewQuery $viewQuery
     */
    public function __construct(CouchbaseViewQuery $viewQuery)
    {
        $this->path = $viewQuery->toString();
    }
}
