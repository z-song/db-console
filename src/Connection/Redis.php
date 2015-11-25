<?php

namespace Encore\Dbconsole\Connection;

use Predis\Client;
use ErrorException;
use Predis\Response\ResponseInterface;

class Redis extends ConnectionAbstract implements ConnectionInterface
{
    public function __construct($name, $config)
    {
        if( ! class_exists('Predis\Client')) {
            throw new \ErrorException("Redis not supported currently, please install 'predis/predis' package.");
        }

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

        if(strtoupper($commandId) == 'SUBSCRIBE') {
            return $this->subscribe(current($arguments));
        }

        $command = $this->connection->createCommand($commandId, $arguments);
        $result  = $this->connection->executeCommand($command);

        if(strtoupper($commandId) == 'INFO') {
            return $this->info($result);
        }

        return $this->formatResult($result);
    }

    public function subscribe($channel)
    {
        if(empty($channel)) {
            throw new ErrorException('please input a channel');
        }

        $pubsub = $this->connection->pubSubLoop()->subscribe($channel);

        foreach ($pubsub as $message)
        {
            foreach(array_values((array)$message) as $key => $val)
            {
                echo ++$key, ") ", is_int($val) ? "(integer) $val" : "\"$val\"", "\n";
            }
        }
    }

    public function info($result)
    {
        return json_encode($result, JSON_PRETTY_PRINT);
    }

    public function formatResult($result)
    {
        if($result instanceof ResponseInterface) {
            return $result->__toString();
        }

        if(is_array($result) && $this->isAssociate($result)) {
            return [$result];
        }

        return $result;
    }

    public function isAssociate($arr)
    {
        return (bool) count(array_filter(array_keys($arr), 'is_string'));
    }
}