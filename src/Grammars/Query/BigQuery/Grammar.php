<?php


namespace Juanparati\LaravelOdbc\Grammars\Query\BigQuery;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar as BaseGrammar;
use Juanparati\LaravelOdbc\Concerns\Grammars\BigQuery as BigQueryConcern;
use \RuntimeException;


/**
 * Class Grammar.
 *
 * @package Juanparati\LaravelOdbc\Grammars\Query
 */
class Grammar extends BaseGrammar
{

    use BigQueryConcern;


    /**
     * The components that make up a select clause.
     *
     * @var string[]
     */
    protected $selectComponents = [
        'aggregate',
        'columns',
        'from',
        'joins',
        'wheres',
        'groups',
        'havings',
        'qualify',      // @ToDo: Pending
        'window',       // @ToDo: Pending
        'orders',
        'limit',
        'offset',
    ];


    /**
     * Add a "where null" clause to the query.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function whereNull(Builder $query, $where)
    {
        if ($this->isJsonSelector($where['column'])) {
            return $this->wrapJsonSelector($where['column']) . ' is null';
        }

        return parent::whereNull($query, $where);
    }


    /**
     * Add a "where not null" clause to the query.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $where
     * @return string
     */
    protected function whereNotNull(Builder $query, $where)
    {
        if ($this->isJsonSelector($where['column'])) {
            return $this->wrapJsonSelector($where['column']) . ' is not null';
        }

        return parent::whereNotNull($query, $where);
    }


    /**
     * Where by date part.
     *
     * @param $type
     * @param Builder $query
     * @param $where
     * @return string
     */
    protected function dateBasedWhere($type, Builder $query, $where)
    {
        $value = $this->parameter($where['value']);

        return 'extract('.$type.' from '.$this->wrap($where['column']).') '.$where['operator'].' '.$value;
    }


    /**
     * Compile a "JSON contains" statement into SQL.
     *
     * @param  string  $column
     * @param  string  $value
     * @return string
     */
    protected function compileJsonContains($column, $value)
    {
        return $this->wrapJsonSelector($column) . ' = ' . $value;
    }


    /**
     * Compile a "JSON length" statement into SQL.
     *
     * Note: This function only works with arrays.
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  string  $value
     * @return string
     */
    protected function compileJsonLength($column, $operator, $value)
    {
        [$field, $path] = $this->wrapJsonFieldAndPath($column);

        return 'array_length(json_array_value('.$field.$path.')) ' . $operator . ' ' . $value;
    }


    /**
     * Compile the random statement into SQL.
     *
     * @param  string  $seed
     * @return string
     */
    public function compileRandom($seed)
    {
        // BigQuery Rand function doesn't support seed
        return 'RAND()';
    }


    /**
     * Compile an insert statement into SQL.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $values
     * @return string
     */
    public function compileInsert(Builder $query, array $values)
    {
        if (empty($values)) {
            $values = [[]];
        }

        return parent::compileInsert($query, $values);
    }


    /**
     * Compile an "upsert" statement into SQL.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  array  $values
     * @param  array  $uniqueBy
     * @param  array  $update
     * @return string
     */
    public function compileUpsert(Builder $query, array $values, array $uniqueBy, array $update)
    {
        $values = collect($values[0]);

        $selectFields = $values->map(function ($value, $key) {
            return $this->parameter($value) . ' as ' . $this->wrap($key);
        })->implode(', ');

        $insertFields = $values->map(function ($value, $key) {
            return $this->wrap($key) . ' = ' . $this->wrap('__update2_.'.$key);
        })->implode(', ');

        $insertKeys = $values->keys()
            ->map([$this, 'wrap'])
            ->implode(', ');

        $uniqueFields = collect($uniqueBy)->map(function ($value) {
            return $this->wrap('__update1_.'.$value) . '=' . $this->wrap('__update2_.'.$value);
        })->implode(' and ');

        $updateFields = $values->filter(function ($value, $key) use ($update){
            return in_array($key, $update);
        })
            ->map(function ($value, $key) {
                return $this->wrap($key) . ' = ' . $this->wrap('__update2_.'.$key);
            })
            ->implode(', ');

        return 'merge into ' .  $this->wrapTable($query->from) . ' as __update1_'
            . ' using (select ' . $selectFields . ') as __update2_'
            . ' on ' . $uniqueFields
            . ' when not matched then'
            . ' insert ('  . $insertKeys . ') values (' . $insertFields . ')'
            . ' when matched then'
            . ' update set ' . $updateFields;
    }


    /**
     * Prepare the bindings for an update statement.
     *
     * Booleans, integers, and doubles are inserted into JSON updates as raw values.
     *
     * @param  array  $bindings
     * @param  array  $values
     * @return array
     */
    public function prepareBindingsForUpdate(array $bindings, array $values)
    {
        $values = collect($values)->reject(function ($value, $column) {
            return $this->isJsonSelector($column) && is_bool($value);
        })->map(function ($value) {
            return is_array($value) ? json_encode($value) : $value;
        })->all();

        return parent::prepareBindingsForUpdate($bindings, $values);
    }


    /**
     * Compile the lock into SQL.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  bool|string  $value
     * @return string
     */
    protected function compileLock(Builder $query, $value)
    {
        throw new RuntimeException('This database engine does not support inserting while ignoring errors.');
    }


    /**
     * Determine if the grammar supports savepoints.
     *
     * @return bool
     */
    public function supportsSavepoints()
    {
        return false;
    }


    /**
     * Wrap the given JSON selector.
     *
     * @param  string  $value
     * @return string
     */
    protected function wrapJsonSelector($value)
    {
        [$field, $path] = $this->wrapJsonFieldAndPath($value);

        return 'json_query('.$field.$path.')';
    }


    /**
     * Wrap the given JSON selector for boolean values.
     *
     * @param  string  $value
     * @return string
     */
    protected function wrapJsonBooleanSelector($value)
    {
        return $this->wrapJsonSelector($value);
    }

}