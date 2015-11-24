<?php

namespace Dbconsole\Connection;

abstract class ConnectionAbstract
{
    protected $name;

    protected $config;

    protected $connection;

    public function getPrompt()
    {
        return "{$this->config['driver']}[{$this->name}]";
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function queryClosed($query)
    {
        return true;
    }

}