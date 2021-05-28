<?php

namespace Juanparati\LaravelOdbc\Drivers;

use Juanparati\LaravelOdbc\PDO\SnowflakeCustomStatement;
use PDO;
use Juanparati\LaravelOdbc\OdbcConnector;
use Juanparati\LaravelOdbc\Contracts\OdbcDriver;
use Juanparati\LaravelOdbc\Drivers\Snowflake\Connection as SnowflakeConnection;

/**
 * Snowflake Connector
 * Inspiration: https://github.com/jenssegers/laravel-mongodb.
 */
class Snowflake extends OdbcConnector implements OdbcDriver
{
    public static function registerDriver()
    {
        return function ($config, $name) {
            $config['database'] = $config['database'] ?? null;

            $pdo = (new self())->connect($config);
            $pdo->setAttribute(PDO::ATTR_STATEMENT_CLASS, [SnowflakeCustomStatement::class, [$pdo]]);
            $connection = new SnowflakeConnection($pdo, $config['database'], isset($config['prefix']) ? $config['prefix'] : '', $config);

            // set default fetch mode for PDO
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, $connection->getFetchMode());

            return $connection;
        };
    }
}
