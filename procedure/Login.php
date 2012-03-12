<?php

namespace procedure;

class Login extends Procedure
{
    public function execute($parameters = array()) {
        $result = array(
            'status' => 'success', //or error
            'data' => 'Valid user'
        );

        return $result;
    }
}