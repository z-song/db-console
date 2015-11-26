<?php

namespace Encore\Dbconsole\TabCompletion\Matcher;

use Encore\Dbconsole\TabCompletion\AutoCompleter;

trait EloquentMatcherTrait {

    private $keyWords = [
        'select',
        'from',
        'where',
        'joins',
        'in',
        'and',
        'or',
        'limit',
        'offset',
        'unions',
        'lock',
    ];

    private $matchMode;

    private $table;

    public function getMatches($buffer)
    {
        $connection = AutoCompleter::getShell()->getConnection();

        if($this->matchMode == 'table') {
            $tables = $connection->query('show tables;');

            $matches = array_map(function($table) {
                return $table->Tables_in_test;
            }, $tables);
        }

        if($this->matchMode == 'column') {
            $columns = $connection->query("desc {$this->table}");

            $matches = array_map(function($table) {
                return $table->Field;
            }, $columns);
        }

        return $matches;
    }

    public function hasMatched($buffer)
    {
        if(preg_match('/^select[\s,\w*]+from\s*|^insert\s+into\s*/', $buffer) == 1) {
            $this->matchMode = 'table';
        }

        if(preg_match('/([^\s]+)\s+where\s*|^update\s+([^\s]+).*?where\s*/', $buffer, $match) == 1)  {

            $this->table = $match[1] ?: $match[2];

            $this->matchMode = 'column';
        }

        return (bool) $this->matchMode;
    }
}