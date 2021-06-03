<?php

namespace Juanparati\LaravelOdbc\Drivers\Snowflake;

use PDO;
use PDOStatement;
use DateTimeInterface;
use Juanparati\LaravelOdbc\Grammars\Query\Snowflake\Grammar as QueryGrammar;
use Juanparati\LaravelOdbc\Grammars\Schema\Snowflake\Grammar as SchemaGrammar;
use Juanparati\LaravelOdbc\Processors\SnowflakeProcessor as Processor;
use Juanparati\LaravelOdbc\OdbcConnection;


/**
 * Class Connection.
 *
 * @package Juanparati\LaravelOdbc\Drivers\Snowflake
 */
class Connection extends OdbcConnection
{

    /**
     * Custom schema grammar.
     *
     * @return \Illuminate\Database\Schema\Grammars\Grammar
     */
    protected function getCustomSchemaGrammar()
    {
        return new SchemaGrammar();
    }


    /**
     * Custom query grammar.
     *
     * @return \Illuminate\Database\Query\Grammars\Grammar
     */
    protected function getCustomQueryGrammar()
    {
        return new QueryGrammar();
    }


    /**
     * Custom post processor.
     *
     * @return \Illuminate\Database\Query\Processors\Processor
     */
    protected function getCustomPostProcessor()
    {
        return new Processor();
    }

    /**
     * Execute an SQL statement and return the boolean result.
     *
     * @param string $query
     * @param array  $bindings
     *
     * @return bool
     */
    public function statement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return true;
            }

            // only use the prepare if there are bindings
            if (0 === count($bindings)) {
                $affected = $this->getPdo()->query($query);

                if (false === (bool) $affected) {
                    $err = $affected->errorInfo();
                    if ('00000' === $err[0] || '01000' === $err[0]) {
                        return true;
                    }
                }

                return (bool) $affected;
            }

            $statement = $this->getPdo()->prepare($query);

            $this->bindValues($statement, $this->prepareBindings($bindings));

            $this->recordsHaveBeenModified();

            return $statement->execute();
        });
    }

    /**
     * Bind values to their parameters in the given statement.
     *
     * @param PDOStatement $statement
     * @param array        $bindings
     *
     * @return void
     */
    public function bindValues($statement, $bindings)
    {
        foreach ($bindings as $key => $value) {
            $type = PDO::PARAM_STR;
            if (is_bool($value)) {
                $value = $value ? 'TRUE' : 'FALSE';
            } elseif (is_numeric($value)) {
                $type = PDO::PARAM_INT;
            }

            $statement->bindValue(
                is_string($key) ? $key : $key + 1,
                $value,
                $type
            );
        }
    }

    /**
     * Prepare the query bindings for execution.
     *
     * @return array
     */
    public function prepareBindings(array $bindings)
    {
        $grammar = $this->getQueryGrammar();

        foreach ($bindings as $key => $value) {
            // We need to transform all instances of DateTimeInterface into the actual
            // date string. Each query grammar maintains its own date string format
            // so we'll just ask the grammar for the format to get from the date.
            if ($value instanceof DateTimeInterface) {
                $bindings[$key] = $value->format($grammar->getDateFormat());
            } elseif (is_bool($value)) {
                $bindings[$key] = (bool) $value;
            } elseif (is_float($value)) {
                $bindings[$key] = (float) $value;
            } elseif (is_numeric($value)) {
                $bindings[$key] = (int) $value;
            }
        }

        return $bindings;
    }

}
