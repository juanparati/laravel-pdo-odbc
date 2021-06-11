<?php


namespace Juanparati\LaravelOdbc\Tests\Unit\BigQuery;


use Illuminate\Support\Facades\DB;
use Juanparati\LaravelOdbc\OdbcServiceProvider;
use Orchestra\Testbench\TestCase;


/**
 * Class TestBasicConnection.
 *
 * @package Juanparati\LaravelOdbc\Tests\Unit\BigQuery
 */
class BasicConnectionTest extends TestCase
{

    /**
     * Load service providers.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return string[]
     */
    protected function getPackageProviders($app)
    {
        return [OdbcServiceProvider::class];
    }


    /**
     * Setup environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $conf = require_once (__DIR__ . '/../../secrets/bigquery_conf.php');

        config([
            'database' => [
                'default' => 'bigquery',
                'connections' => [
                    'bigquery' => [
                        'driver'   => 'bigquery',
                        'dsn'      => $conf['dsn'],
                        'database' => $conf['database'],
                        'options' => [
                            \PDO::ODBC_ATTR_USE_CURSOR_LIBRARY => \PDO::ODBC_SQL_USE_DRIVER,
                        ]
                    ]
                ]
            ]
        ]);
    }


    /**
     * Test a basic connection.
     *
     */
    public function testBasicConnection() {
        $result = DB::select('select true as result');

            $this->assertEquals("1", $result[0]->result);
    }


}