<?php

namespace service;

abstract class Service
{
    /**
     * Execute the Service
     * 
     * @param array $param
     * @return array 
     */
    abstract public function execute($parameters = array());

    protected function getEntityManager()
    {
        include __DIR__.'/../bootstrap.php';
        return $em;
    }

    /**
     * Verify required parameters
     * 
     * @param array $parameters
     * @param array $required
     * @return boolean 
     */
    protected function checkParameters($parameters, $required) {
        foreach($required as $r) {
            if (!$parameters[$r]) {
                return false;
            }
        }
        return true;
    }
}
