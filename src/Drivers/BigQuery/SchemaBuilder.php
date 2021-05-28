<?php


namespace Juanparati\LaravelOdbc\Drivers\BigQuery;

use Illuminate\Database\Schema\Builder as BaseBuilder;


/**
 * Class SchemaBuilder.
 *
 * @ToDo: Pending
 * @package Juanparati\LaravelOdbc\Drivers\BigQuery
 */
class SchemaBuilder extends BaseBuilder
{

    /**
     * Determine if the given table exists.
     *
     * @param  string  $table
     * @return bool
     */
    public function hasTable($table)
    {
        $table = $this->connection->getTablePrefix() . $table;

        return count($this->connection->select(
                $this->grammar->compileTableExists(),
                [$this->connection->getDatabaseName(), $table]
            )) > 0;
    }

    /**
     * Get the column listing for a given table.
     *
     * @param  string  $table
     * @return array
     */
    public function getColumnListing($table)
    {
        $table = $this->connection->getTablePrefix() . $table;

        $results = $this->connection->select(
            $this->grammar->compileColumnListing(),
            [$this->connection->getDatabaseName(), $table]
        );

        return $this->connection->getPostProcessor()->processColumnListing($results);
    }


    /**
     * Get all of the table names for the database.
     *
     * @return array
     */
    public function getAllTables()
    {
        $tables = $this->connection->select($this->grammar->compileGetAllTables());

        return array_column($tables, 'name');
    }

    /**
     * Get all of the view names for the database.
     *
     * @return array
     */
    public function getAllViews()
    {
        return $this->connection->select(
            $this->grammar->compileGetAllViews()
        );
    }

    /**
     * Get the data type for the given column name.
     *
     * @param  string  $table
     * @param  string  $column
     * @return string
     */
    public function getColumnType($table, $column)
    {
        $table = $this->connection->getTablePrefix() . $table;

        $record = $this->connection->select(
            $this->grammar->compileGetColumnType(),
            [$table, $column]
        );
        $record = reset($record);

        if (!$record) {
            return null;
        }

        $record->numeric_precision = (int) $record->numeric_precision;
        $record->numeric_scale = (int) $record->numeric_scale;

        return $record;
    }

}