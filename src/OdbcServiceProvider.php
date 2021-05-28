<?php

namespace Juanparati\LaravelOdbc;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\DatabaseManager;


/**
 * Class OdbcServiceProvider.
 *
 * @package Juanparati\LaravelOdbc
 */
class OdbcServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->resolving('db', function ($db) {
            /* @var DatabaseManager $db */
            $db->extend('odbc', OdbcConnector::registerDriver());
            $db->extend('snowflake', Drivers\Snowflake::registerDriver());
            $db->extend('bigquery', Drivers\BigQuery::registerDriver());
        });
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        Model::setConnectionResolver($this->app['db']);
        Model::setEventDispatcher($this->app['events']);
    }
}
