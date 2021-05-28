<?php

namespace Juanparati\LaravelOdbc;

use Illuminate\Database\Connection;



/**
 * Class OdbcConnection.
 *
 * @package Juanparati\LaravelOdbc
 */
class OdbcConnection extends Connection
{

    /**
     * Get the default query grammar.
     *
     * @return \Illuminate\Database\Query\Grammars\Grammar
     */
    public function getDefaultQueryGrammar()
    {
        $queryGrammar = $this->getConfig('options.grammar.query');

        if ($queryGrammar) {
            return new $queryGrammar();
        }

        return $this->getCustomQueryGrammar();
    }


    /**
     * Get the default schema grammar.
     *
     * @return \Illuminate\Database\Schema\Grammars\Grammar|mixed
     */
    public function getDefaultSchemaGrammar()
    {
        $schemaGrammar = $this->getConfig('options.grammar.schema');

        if ($schemaGrammar) {
            return new $schemaGrammar();
        }

        return $this->getCustomSchemaGrammar();
    }

    /**
     * Get current fetch mode from the connection.
     *
     * Default should be: PDO::FETCH_OBJ.
     *
     * @return int
     */
    public function getFetchMode() : int
    {
        return $this->fetchMode;
    }


    /**
     * Get the default post processor instance.
     *
     * @return \Illuminate\Database\Query\Processors\Processor|mixed
     */
    protected function getDefaultPostProcessor()
    {
        $processor = $this->getConfig('options.processor');

        if ($processor) {
            return new $processor();
        }

        return $this->getCustomPostProcessor();
    }


    /**
     * Custom schema grammar.
     *
     * @return \Illuminate\Database\Schema\Grammars\Grammar
     */
    protected function getCustomSchemaGrammar()
    {
        return parent::getDefaultSchemaGrammar();
    }


    /**
     * Custom query grammar.
     *
     * @return \Illuminate\Database\Query\Grammars\Grammar
     */
    protected function getCustomQueryGrammar()
    {
        return parent::getDefaultQueryGrammar();
    }


    /**
     * Custom post processor.
     *
     * @return \Illuminate\Database\Query\Processors\Processor
     */
    protected function getCustomPostProcessor()
    {
        return parent::getDefaultPostProcessor();
    }
}
