<?php


namespace Juanparati\LaravelOdbc\Concerns\Grammars;


/**
 * This code is shared between the Query and Schema grammar.
 * Mainly for correcting the values and columns.
 *
 * Values: are wrapped within single quotes.
 * Columns and Table names: are wrapped within tick quotes.
 */
trait BigQuery
{

    /**
     * Reserved column names.
     *
     * @var string[]
     */
    protected $reservedColumnNames = [
        '_TABLE_',
        '_FILE_',
        '_PARTITION'
    ];


    /**
     * Wrap a single string in keyword identifiers.
     *
     * @param  string  $value
     * @return string
     */
    protected function wrapValue($value)
    {
        return $value === '*' ? $value : '`'.str_replace('`', '``', $value).'`';
    }

}