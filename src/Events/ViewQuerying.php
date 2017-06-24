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

use Couchbase\ViewQuery;

/**
 * Class ViewQuerying
 *
 * @author Yuuki Takezawa<yuuki.takezawa@comnect.jp.net>
 */
class ViewQuerying
{
    /** @var ViewQuery */
    private $viewQuery;

    /**
     * ViewQuerying constructor.
     *
     * @param ViewQuery $viewQuery
     */
    public function __construct(ViewQuery $viewQuery)
    {
        $this->viewQuery = $viewQuery;
    }

    /**
     * @return ViewQuery
     */
    public function viewQuery(): ViewQuery
    {
        return $this->viewQuery;
    }
}
