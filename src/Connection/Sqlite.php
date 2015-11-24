<?php

namespace Dbconsole\Connection;

use Dbconsole\Connection;

class Sqlite extends ConnectionAbstract implements ConnectionInterface
{
    use EloquentTrait;
}