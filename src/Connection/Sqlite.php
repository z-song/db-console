<?php

namespace Encore\Dbconsole\Connection;

class Sqlite extends ConnectionAbstract implements ConnectionInterface
{
    use EloquentTrait;
}