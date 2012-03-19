<?php
namespace model;

use Doctrine\ORM\Mapping as ORM;

abstract class Entity
{
   /**
     * @var DateTime $created
     *
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var DateTime $updated
     *
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     * Get data was created.
     *
     * @return DateTime
     */

    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Get date entity was updated.
     *
     * @return DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set date entity was updated.
     * @param Datetime
     * @return void
     */
    public function setUpdated($date)
    {
        $this->updated = $date;
    }

    /**
     * Set date entity was created.
     * @param Datetime
     * @return void
     */
    public function setCreated($date)
    {
        $this->created = $date;
    }

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