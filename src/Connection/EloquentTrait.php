<?php

namespace Encore\Dbconsole\Connection;

use Illuminate\Support\Str;
use Illuminate\Database\Capsule\Manager as Capsule;

trait EloquentTrait
{
    private $manager;

    private $result;

    private $log;

    public function __construct($name, $config)
    {
        $this->name = $name ?: 'default';

        $this->addConnection($config);

        $this->bootEloquent();
    }

    public function addConnection($config)
    {
        $this->config = $config;

        $this->manager = new Capsule;

        $this->manager->addConnection($config);
    }

    public function bootEloquent()
    {
        $this->manager->bootEloquent();

        $this->manager->setAsGlobal();

        $this->connection = $this->manager->connection();
    }

    public function query($sql)
    {
        if(empty($sql)) return;

        $this->connection->enableQueryLog();

        $this->result = $this->connection->select(str_replace([';', "\G"], '', $sql));

        $this->log = current($this->connection->getQueryLog());

        if(Str::contains($sql, "\G")) {
            $this->result = json_encode($this->result, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        }

        return $this->result;
    }

    public function queryClosed($query)
    {
        return Str::contains($query, ';');
    }

    public function appendResult()
    {
        return sprintf("%d row in set (%s sec)\r\n", count($this->result), number_format($this->log['time'] / 1000, 2));
    }
}