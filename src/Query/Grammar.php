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

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar as IlluminateGrammar;

/**
 * Class Grammar
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
     * {@inheritdoc}
     */
    public function compileUpdate(Builder $query, $values)
    {
        $table = $this->wrapTable($query->from);

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

        return trim("update {$table}{$joins} set $columns $where RETURNING *");
    }

    /**
     * {@inheritdoc}
     */
    public function compileInsert(Builder $query, array $values)
    {
        $table = $this->wrapTable($query->from);

        if (!is_array(reset($values))) {
            $values = [$values];
        }

        $columns = $this->columnize(array_keys(reset($values)));

        $parameters = [];

        foreach ($values as $record) {
            $parameters[] = '(' . $this->parameterize($record) . ')';
        }

        $parameters = implode(', ', $parameters);

        return "insert into $table ($columns) values $parameters RETURNING *";
    }

    /**
     * {@inheritdoc}
     */
    public function compileDelete(Builder $query)
    {
        $table = $this->wrapTable($query->from);

        $where = is_array($query->wheres) ? $this->compileWheres($query) : '';

        return trim("delete from {$table} {$where} RETURNING *");
    }

    /**
     * supported N1QL upsert query
     *
     * @param Builder $query
     * @param array   $values
     *
     * @return string
     */
    public function compileUpsert(Builder $query, array $values)
    {
        $table = $this->wrapTable($query->from);

        if (!is_array(reset($values))) {
            $values = [$values];
        }

        $columns = $this->columnize(array_keys(reset($values)));

        $parameters = [];

        foreach ($values as $record) {
            $parameters[] = '(' . $this->parameterize($record) . ')';
        }

        $parameters = implode(', ', $parameters);

        return "UPSERT into $table ($columns) values $parameters RETURNING *";
    }
}
