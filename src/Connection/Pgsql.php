<?php

namespace Dbconsole\Connection;

use Dbconsole\Connection;

class Pgsql extends ConnectionAbstract implements ConnectionInterface
{
    use EloquentTrait;
}