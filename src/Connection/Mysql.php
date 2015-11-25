<?php

namespace Encore\Dbconsole\Connection;

class Mysql extends ConnectionAbstract implements ConnectionInterface
{
    use EloquentTrait;
}