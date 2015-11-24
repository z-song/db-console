<?php

require __DIR__ . '/vendor/autoload.php';

use Dbconsole\Shell;

$shell = new Shell(__DIR__ . '/config/database.php1');

$shell->setConnection('redis');

$shell->run();