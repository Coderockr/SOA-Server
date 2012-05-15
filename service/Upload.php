<?php

namespace service;

use model;

class Upload extends Service
{
    public function execute($parameters = array()) {

        $required = array('path');
        if (!$this->checkParameters($parameters, $required)) {
            $result = array(
                'status' => 'error', 
                'data' => 'Missing parameters'
            );
            return $result;
        }
        foreach ($_FILES as $f) {
            $tmpName = $f['tmp_name'][0];
            $name = $f['name'][0];
            $extension = pathinfo($name, PATHINFO_EXTENSION);
            $newName = md5($name . date('mdsissis')) . '.' . $extension;
            move_uploaded_file($tmpName, $parameters['path'] . $newName);
            $result = array(
                'status' => 'success', 
                'data' => json_encode(array('file' => $newName))
            );
            return $result;
        }
    }
}