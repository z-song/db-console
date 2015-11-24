<?php

return [

    'default' => 'mysql',

    'connections' => [

        'sqlite' => [
            'driver'   => 'sqlite',
            'database' => __DIR__ . '/../../database.sqlite',
            'prefix'   => '',
        ],

        'mysql' => [
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'test',
            'username'  => '',
            'password'  => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => false,
        ],

        'pgsql' => [
            'driver'   => 'pgsql',
            'host'     => 'localhost',
            'database' => 'forge',
            'username' => 'forge',
            'password' => '',
            'charset'  => 'utf8',
            'prefix'   => '',
            'schema'   => 'public',
        ],

        'sqlsrv' => [
            'driver'   => 'sqlsrv',
            'host'     => 'localhost',
            'database' => 'forge',
            'username' => 'forge',
            'password' => '',
            'charset'  => 'utf8',
            'prefix'   => '',
        ],

        'mongodb' => [
            'name'      => 'mongodb',
            'driver'    => 'mongodb',
            'host'      => 'localhost',
            'port'      => 27019,
            'database'  => 'test',
            'username'  => '',
            'password'  => '',
            'slaveOkay' => true,
            'connectTimeoutMS'   => 60*10,
            'connect'   => false
        ],

    ],

    'redis' => [

        'cluster' => false,

        'default' => [
            'host'     => 'localhost',
            'port'     => 6379,
            'database' => 0,
        ],

    ],

];
