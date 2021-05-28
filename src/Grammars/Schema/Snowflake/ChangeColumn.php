<?php

namespace Juanparati\LaravelOdbc\Grammars\Schema\Snowflake;

use Illuminate\Support\Fluent;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\Grammar;


/**
 * Changing actions:
 * - Add column
 * - Delete column
 * - Rename column
 * - Change column type
 *     - type change
 *     - precision change
 *     - null to not null
 *     - not null to null
 */
class ChangeColumn
{
    /**
     * Compile a change column command into a series of SQL statements.
     *
     * @param  \Illuminate\Database\Schema\Grammars\Grammar  $grammar
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @param  \Illuminate\Database\Connection  $connection
     * @return array
     *
     * @throws \RuntimeException
     */
    public static function compile(Grammar $grammar, Blueprint $blueprint, Fluent $command, Connection $connection)
    {
        $type = $command->offsetGet('name'); // can be: change, dropColumn, renameColumn
        $columns = $blueprint->getColumns();

        if ($type === 'dropColumn') {
            return $grammar->compileDropColumn($blueprint, $command);
        } else if ($type === 'renameColumn') {
            return $grammar->compileRenameColumn($blueprint, $command, $connection);
        }

        return $grammar->compileChangeColumn($blueprint, $command);
    }
}
