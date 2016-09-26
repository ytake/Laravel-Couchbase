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

namespace Ytake\LaravelCouchbase\Schema;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Grammars\Grammar;
use Ytake\LaravelCouchbase\Exceptions\NotSupportedException;

/**
 * Trait NotSupportedTrait
 *
 * @codeCoverageIgnore
 *
 * @author Yuuki Takezawa<yuuki.takezawa@comnect.jp.net>
 */
trait NotSupportedTrait
{
    /**
     * {@inheritdoc}
     */
    public function toSql(Connection $connection, Grammar $grammar)
    {
        throw new NotSupportedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    protected function createIndexName($type, array $columns)
    {
        throw new NotSupportedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function addColumn($type, $name, array $parameters = [])
    {
        throw new NotSupportedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function removeColumn($name)
    {
        throw new NotSupportedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    protected function addCommand($name, array $parameters = [])
    {
        throw new NotSupportedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    protected function createCommand($name, array $parameters = [])
    {
        throw new NotSupportedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getChangedColumns()
    {
        throw new NotSupportedException(__METHOD__);
    }
}
