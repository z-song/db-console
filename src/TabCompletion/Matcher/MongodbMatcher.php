<?php

namespace Encore\Dbconsole\TabCompletion\Matcher;

use Encore\Dbconsole\TabCompletion\AutoCompleter;

class MongodbMatcher
{
    private $matchMode;

    private $collection;

    public function getMatches($buffer)
    {
        if($this->matchMode == 'db') {
            $result = AutoCompleter::getShell()->getConnection()->query('db.help');

            preg_match_all("/(db\.[^\s]*\()/", $result, $matches);

            $collections = json_decode($this->getCollectionNames());

            return array_merge($matches[1], array_map(function($col) {return 'db.'.$col;}, $collections));
        }

        if($this->matchMode == 'collection') {
            $result = AutoCompleter::getShell()->getConnection()->query("db.{$this->collection}.help");
            preg_match_all("/\.([a-zA-Z]+\()/", $result, $matches);

            $matches = array_map(function($match){
                return "db.{$this->collection}." . $match;
            }, $matches[1]);

            return $matches;
        }

        return [];
    }

    public function hasMatched($cmd)
    {
        if(preg_match('/^db\.[^\.]*/', $cmd) == 1) {
            $this->matchMode = 'db';
        }

        if(preg_match('/^db\.(\w+)\./', $cmd, $match) == 1) {
            $this->matchMode = 'collection';

            $this->collection = $match[1];
        }

        return (bool) $this->matchMode;
    }

    public function getCollectionNames()
    {
        return AutoCompleter::getShell()->getConnection()->query('db.getCollectionNames()');
    }

}