<?php

namespace Dbconsole\Connection;

use Dbconsole\Connection;

class Mysql extends ConnectionAbstract implements ConnectionInterface
{
    use EloquentTrait;
}