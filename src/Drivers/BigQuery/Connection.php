<?php


namespace Juanparati\LaravelOdbc\Drivers\BigQuery;


use Juanparati\LaravelOdbc\Grammars\Query\BigQuery\Grammar as QueryGrammar;
use Juanparati\LaravelOdbc\Grammars\Schema\BigQuery\Grammar as SchemaGrammar;
use Juanparati\LaravelOdbc\Processors\BigQueryProcessor as Processor;
use Juanparati\LaravelOdbc\OdbcConnection;


/**
 * Class Connection.
 *
 * @package Juanparati\LaravelOdbc\Drivers\BigQuery
 */
class Connection extends OdbcConnection {

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
}