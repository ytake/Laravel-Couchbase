<?php

namespace Ytake\LaravelCouchbase\Query;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar as IlluminateGrammar;

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

        if (isset($query->joins)) {
            $joins = ' ' . $this->compileJoins($query, $query->joins);
        } else {
            $joins = '';
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

        if (! is_array(reset($values))) {
            $values = [$values];
        }

        $columns = $this->columnize(array_keys(reset($values)));

        $parameters = [];

        foreach ($values as $record) {
            $parameters[] = '('.$this->parameterize($record).')';
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
     * @param Builder $query
     * @param array   $values
     *
     * @return string
     */
    public function compileUpsert(Builder $query, array $values)
    {
        $table = $this->wrapTable($query->from);

        if (! is_array(reset($values))) {
            $values = [$values];
        }

        $columns = $this->columnize(array_keys(reset($values)));

        $parameters = [];

        foreach ($values as $record) {
            $parameters[] = '('.$this->parameterize($record).')';
        }

        $parameters = implode(', ', $parameters);

        return "UPSERT into $table ($columns) values $parameters RETURNING *";
    }
}
