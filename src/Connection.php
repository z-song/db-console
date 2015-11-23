<?php

namespace Dbconsole;

use Illuminate\Database\Capsule\Manager as Capsule;

class Connection
{
    private $connection;

    private $dbManager;

    public function __construct($connection = 'default')
    {
        $this->addConnection($connection);

        $this->bootEloquent();
    }

    public function addConnection($connection)
    {
        $config = $this->loadConfig($connection);

        $this->dbManager = new Capsule;

        $this->dbManager->addConnection($config);
    }

    public function bootEloquent()
    {
        $this->dbManager->bootEloquent();

        $this->dbManager->setAsGlobal();

        $this->connection = $this->dbManager->connection();
    }

    public function loadConfig($connection)
    {
        $configPath = realpath(__DIR__.'/config/database.php');

        $config = require $configPath;

        $connection = isset($config['connections'][$connection]) ?
                    $config['connections'][$connection] :
                    $config['connections'][$config[$connection]];

        if(! $connection) {
            throw new \Exception("connection {$connection} not found");
        }

        return $connection;
    }

    public function query($query)
    {
        if(empty($query)) return;

        return $this->connection->select($query);
    }
}