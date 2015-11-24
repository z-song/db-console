<?php

namespace Encore\Dbconsole\Connection;

use MongoClient;
use Dbconsole\Connection;
use Illuminate\Support\Str;

class Mongodb extends ConnectionAbstract implements ConnectionInterface
{
    private $allowedOptions = [
        'connect', 'db', 'password', 'readPreference',
        'readPreferenceTags', 'replicaSet', 'connectTimeoutMS',
        'timeout', 'socketTimeoutMS', 'username', 'w', 'wTimeout'
    ];


    public function __construct($name, $config)
    {
        if( ! class_exists('\MongoClient')) {
            throw new \Exception("MongoDB extension not installed");
        }

        $this->name = $name;

        $this->addConnection($config);
    }

    public function addConnection($config)
    {
        $this->config = $config;

        $server  = $this->buildDsn($config);
        $options = $this->getOptions($config);

        $client = new MongoClient($server, $options);

        $this->connection = $client->selectDB($config['database']);
    }

    public function query($query)
    {
        if(Str::contains($query, '.find(') && ! Str::contains($query, '.toArray(')) {
            $query .= '.toArray()';
        }

        $result = $this->connection->execute($query);

        return json_encode($result['retval'], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
    }

    public function buildDsn($config)
    {
        return "mongodb://{$config['host']}:{$config['port']}";
    }

    public function getOptions($config)
    {
        $options = [];

        foreach($config as $key => $option) {
            if(in_array($key, $this->allowedOptions)) {
                if(empty($option)) continue;

                $options[$key] = $option;
            }
        }

        return $options;
    }
}