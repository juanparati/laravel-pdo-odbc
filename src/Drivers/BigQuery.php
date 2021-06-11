<?php

namespace Juanparati\LaravelOdbc\Drivers;

use Juanparati\LaravelOdbc\OdbcConnector;
use Juanparati\LaravelOdbc\PDO\BigQueryCustomStatement;
use Juanparati\LaravelOdbc\Contracts\OdbcDriver;
use Juanparati\LaravelOdbc\Drivers\BigQuery\Connection as BigQueryConnection;

/**
 * Class BigQuery.
 *
 * @package Juanparati\LaravelOdbc\Drivers
 */
class BigQuery extends OdbcConnector implements OdbcDriver
{
    public static function registerDriver()
    {
        return function ($config, $name) {
            $config['database'] = $config['database'] ?? null;

            $pdo = (new self())->connect($config);
            $pdo->setAttribute(\PDO::ATTR_STATEMENT_CLASS, [BigQueryCustomStatement::class]);
            $connection = new BigQueryConnection($pdo, $config['database'], $config['prefix'] ?? '', $config);

            // set default fetch mode for PDO.
            $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, $connection->getFetchMode());

            return $connection;
        };
    }
}
