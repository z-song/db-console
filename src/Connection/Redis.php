<?php

namespace Dbconsole\Connection;

use Predis\Client;
use Dbconsole\Connection;
use Predis\Response\ResponseInterface;

class Redis extends ConnectionAbstract implements ConnectionInterface
{
    public function __construct($name, $config)
    {
        $this->name = $name ?: 'default';

        $this->addConnection($config);
    }

    public function addConnection($config)
    {
        $this->config = $config;

        $this->connection = new Client($config);
    }

    public function query($query)
    {
        $query = explode(' ', $query);
        $query = array_map('trim', $query);

        $commandId = $query[0];
        $arguments = array_slice($query, 1);

        $command = $this->connection->createCommand($commandId, $arguments);
        $result  = $this->connection->executeCommand($command);

        return $this->formatResult($result);
    }

    public function formatResult($result)
    {
        if($result instanceof ResponseInterface) {
            return $result->__toString();
        }

        if($this->isAssociate($result)) {
            return [$result];
        }

        return $result;
    }

    public function isAssociate($arr)
    {
        return (bool) count(array_filter(array_keys($arr), 'is_string'));
    }
}