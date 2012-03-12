<?php

namespace procedure;

abstract class Procedure
{
    /**
     * Execute the procedure
     * 
     * @param array $param
     * @return array 
     */
    abstract public function execute($parameters = array());
}
