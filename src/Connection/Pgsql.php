<?php

namespace Encore\Dbconsole\Connection;

class Pgsql extends ConnectionAbstract implements ConnectionInterface
{
    use EloquentTrait;
}