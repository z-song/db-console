<?php

namespace Encore\Dbconsole;

class Factory {

    private static $instance;

    private static $config;

    /**
     * Get Factory instance.
     *
     * @return Factory
     */
    public static function getInstance()
    {
        return static::$instance instanceof self ? static::$instance : new self;
    }

    /**
     * @param string $name Connection name
     * @return mixed
     * @throws \Exception
     */
    public static function create($name = null)
    {
        static::$config = static::getInstance()->loadConfig($name);

        $className = __NAMESPACE__ . "\\Connection\\" . ucfirst(static::$config['driver']);

        if(class_exists($className)) {

            return new $className($name, static::$config);
        } else {
            throw new \Exception("Driver " . ucfirst(static::$config['driver']) . " not found");
        }
    }

    /**
     * Load configuration from database with connection name.
     *
     * @param null $name
     * @return mixed
     */
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

    /**
     * Get configuration.
     *
     * @return mixed
     */
    public static function getConfig()
    {
        return static::$config;
    }

    /**
     * Set configuration.
     *
     * @param array $config
     */
    public static function setConfig(array $config)
    {
        static::$config = $config;
    }

}