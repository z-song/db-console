<?php

namespace Encore\Dbconsole;

class Factory {

    private static $instance;

    private static $config;

    protected $connection;

    public static function getInstance()
    {
        return static::$instance instanceof self ? static::$instance : new self;
    }

    /**
     * @param null $name Connection name
     * @return mixed
     * @throws \Exception
     */
    public static function create($name = 'default')
    {
        $config = static::getInstance()->loadConfig($name);

        $className = "Encore\\Dbconsole\\Connection\\" . ucfirst($config['driver']);

        if(class_exists($className)) {

            return new $className($name, $config);
        } else {
            throw new \Exception("Driver " . ucfirst($config['driver']) . " not found");
        }
    }

    protected function loadConfig($name = null)
    {
        if( ! is_array(static::$config)) {
            $configPath = realpath(__DIR__ . '/config/database.php');

            static::$config = require $configPath;
        }

        if(strpos($name, 'redis') === 0) {

            $config = static::$config['redis']['default'];
            $config['driver'] = 'redis';

        } else {

            if(isset(static::$config['connections'][$name])) {
                $config = static::$config['connections'][$name];
            } else {
                $config = static::$config['connections'][static::$config['default']];
            }
        }

        return $config;
    }

    public static function setConfig(array $config)
    {
        static::$config = $config;
    }

}