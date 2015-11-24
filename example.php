<?php

require __DIR__ . '/vendor/autoload.php';

use Encore\Dbconsole\Shell;

$shell = new Shell(__DIR__ . '/config/database.php');

//$shell->setConnection('redis');

$shell->run();