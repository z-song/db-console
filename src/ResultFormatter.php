<?php

namespace Dbconsole;


class ResultFormatter {

    private $result;

    public function __construct(array $result)
    {
        $this->result = $result;
    }

    public function output()
    {
        return $this->__toString();
    }

    public function __toString()
    {
        return json_encode($this->result);
    }
}