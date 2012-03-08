<?php

namespace model;

abstract class Entity
{

    /**
     * Convert an Entity to Json
     * 
     * 
     * @return mixed 
     */
    public function toJson()
    {
        $class = new \ReflectionClass($this);
        $methods = $class->getMethods();
        $data = array();
        foreach($class->getProperties() as $p) {
            $name = $p->getName();
            $method = 'get'. ucfirst($name);
            if ($class->hasMethod($method)) {
                $data[$name] = call_user_func(array($this, $method));    
            }
        }
        return json_encode($data);
    }

    /**
     * Set entity data
     * 
     * @param array $data
     * 
     * @return Entity
     */
    public function set($data)
    {
        $class = new \ReflectionClass($this);
        $methods = $class->getMethods();
        foreach ($data as $key => $value) {
            $method = 'set'. ucfirst($key);
            if ($class->hasMethod($method)) {
               call_user_func(array($this, $method), $value); 
            }
        }
        return $this;
    }

}