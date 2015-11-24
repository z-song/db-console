<?php

namespace Encore\Dbconsole\Connection;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Str;

trait EloquentTrait
{
    private $manager;

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

    public function query($query)
    {

        if(empty($query)) return;

        $result = $this->connection->select(str_replace([';', "\G"], '', $query));

        if(Str::contains($query, "\G")) {
            $result = json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        }

        return $result;
    }

    public function queryClosed($query)
    {
        return Str::contains($query, ';');
    }
}