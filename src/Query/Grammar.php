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

use Illuminate\Database\Query\Builder;
use Ytake\LaravelCouchbase\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Grammars\Grammar as IlluminateGrammar;

/**
 * Class Grammar.
 *
 * @author Yuuki Takezawa<yuuki.takezawa@comnect.jp.net>
 */
class Grammar extends IlluminateGrammar
{
    /**
     * {@inheritdoc}
     */
    protected function wrapValue($value)
    {
        if ($value === '*') {
            return $value;
        }

        return $value;
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    protected function wrapKey($value)
    {
        if (is_null($value)) {
            return;
        }

        return '"' . str_replace('"', '""', $value) . '"';
    }

    /**
     * {@inheritdoc}
     *
     * notice: supported set query only
     */
    public function compileUpdate(Builder $query, $values)
    {
        // keyspace-ref:
        $table = $this->wrapTable($query->from);
        // use-keys-clause:
        $keyClause = $this->wrapKey($query->key);
        // returning-clause
        $returning = implode(', ', $query->returning);

        $columns = [];

        foreach ($values as $key => $value) {
            $columns[] = $this->wrap($key) . ' = ' . $this->parameter($value);
        }

        $columns = implode(', ', $columns);

        $joins = '';
        if (isset($query->joins)) {
            $joins = ' ' . $this->compileJoins($query, $query->joins);
        }
        $where = $this->compileWheres($query);

        return trim("update {$table} USE KEYS {$keyClause} {$joins} set $columns $where RETURNING {$returning}");
    }

    /**
     * {@inheritdoc}
     */
    public function compileInsert(Builder $query, array $values)
    {
        // keyspace-ref:
        $table = $this->wrapTable($query->from);
        // use-keys-clause:
        $keyClause = $this->wrapKey($query->key);
        // returning-clause
        $returning = implode(', ', $query->returning);

        if (!is_array(reset($values))) {
            $values = [$values];
        }
        $parameters = [];

        foreach ($values as $record) {
            $parameters[] = '(' . $this->parameterize($record) . ')';
        }
        $parameters = (!$keyClause) ? implode(', ', $parameters) : "({$keyClause}, \$parameters)";
        $keyValue = (!$keyClause) ? null : '(KEY, VALUE)';

        return "insert into {$table} {$keyValue} values $parameters RETURNING {$returning}";
    }

    /**
     * {@inheritdoc}
     *
     * @see http://developer.couchbase.com/documentation/server/4.1/n1ql/n1ql-language-reference/delete.html
     */
    public function compileDelete(Builder $query)
    {
        // keyspace-ref:
        $table = $this->wrapTable($query->from);
        // use-keys-clause:
        $keyClause = null;
        if ($query->key) {
            $key = $this->wrapKey($query->key);
            $keyClause = "USE KEYS {$key}";
        }
        // returning-clause
        $returning = implode(', ', $query->returning);
        $where = is_array($query->wheres) ? $this->compileWheres($query) : '';

        return trim("delete from {$table} {$keyClause} {$where} RETURNING {$returning}");
    }

    /**
     * @param QueryBuilder $query
     * @param array        $values
     *
     * @return string
     */
    public function compileUpsert(QueryBuilder $query, array $values): string
    {
        // keyspace-ref:
        $table = $this->wrapTable($query->from);
        // use-keys-clause:
        $keyClause = $this->wrapKey($query->key);
        // returning-clause
        $returning = implode(', ', $query->returning);

        if (!is_array(reset($values))) {
            $values = [$values];
        }
        $parameters = [];

        foreach ($values as $record) {
            $parameters[] = '(' . $this->parameterize($record) . ')';
        }
        $parameters = (!$keyClause) ? implode(', ', $parameters) : "({$keyClause}, \$parameters)";
        $keyValue = (!$keyClause) ? null : '(KEY, VALUE)';

        return "UPSERT INTO {$table} {$keyValue} VALUES $parameters RETURNING {$returning}";
    }
}
