<?php

namespace Encore\Dbconsole\TabCompletion\Matcher;

use Illuminate\Support\Str;
use Predis\Profile\Factory;

class RedisMatcher {

    private $supportedCommands;

    public function __construct()
    {
        $this->supportedCommands =
            array_keys(Factory::getDefault()->getSupportedCommands());
    }

    public function getMatches($buffer)
    {
        $buffer = strtoupper($buffer);

        $matches = array_filter($this->supportedCommands, function($cmd) use ($buffer) {
            return Str::startsWith($cmd, $buffer);
        });

        $matches = array_merge($matches, array_map('strtolower', $matches));

        return empty($matches) ? [] : $matches;
    }

    public function hasMatched($buffer)
    {
        foreach($this->supportedCommands as $command) {
            if(Str::startsWith($command, strtoupper($buffer))) {
                return true;
            }
        }

        return false;
    }

}