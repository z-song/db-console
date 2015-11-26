<?php

namespace Encore\Dbconsole\TabCompletion;

use Encore\Dbconsole\Factory;
use Encore\Dbconsole\Shell;

class AutoCompleter {

    private static $shell;

    public function __construct(Shell $shell)
    {
        static::$shell = $shell;
    }

    public function activate()
    {
        readline_completion_function(array(&$this, 'callback'));
    }

    public function callback($input, $index)
    {
        return $this->processCallback($input, $index, readline_info());
    }

    public function processCallback($input, $index, $info = [])
    {
        $matches = [];

        if(! $matcher = $this->getMatcher()) {
            return $matches;
        }

        $line = substr($info['line_buffer'], 0, $info['end']);

        if($matcher->hasMatched($line)) {
            $matches = $matcher->getMatches($line);
        }

        return $matches;
    }

    public function getMatcher()
    {
        $config = Factory::getConfig();

        $matcherClassName = __NAMESPACE__ ."\\Matcher\\" . ucfirst($config['driver']) . "Matcher";

        if(class_exists($matcherClassName)) {
            return new $matcherClassName();
        }

        return false;
    }

    public static function getShell()
    {
        return static::$shell;
    }

    public function __destruct()
    {
        if (function_exists('readline_callback_handler_remove')) {
            readline_callback_handler_remove();
        }
    }
}