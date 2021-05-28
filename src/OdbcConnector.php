<?php

namespace Juanparati\LaravelOdbc;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Juanparati\LaravelOdbc\Contracts\OdbcDriver;
use Illuminate\Database\Connectors\Connector;
use Illuminate\Database\Connectors\ConnectorInterface;

class OdbcConnector extends Connector implements ConnectorInterface, OdbcDriver
{
    /**
     * Establish a database connection.
     *
     * @return \PDO
     *
     * @internal param array $options
     */
    public function connect(array $config)
    {
        $options = $this->getOptions($config);

        $dsn = Arr::get($config, 'dsn');

        if (! Str::contains('odbc:', $dsn)) {
            $dsn = 'odbc:'.$dsn;
        }

        $connection = $this->createConnection($dsn, $config, $options);

        return $connection;
    }

    public static function registerDriver()
    {
        return function ($config, $name) {
            $config['database'] = $config['database'] ?? null;

            $pdoConnection = (new self())->connect($config);
            $connection = new OdbcConnection($pdoConnection, $config['database'], isset($config['prefix']) ? $config['prefix'] : '', $config);

            return $connection;
        };
    }
}
