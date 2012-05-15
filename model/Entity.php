<?php

namespace model;

use Doctrine\ORM\Mapping as ORM,
    Doctrine\ORM\PersistentCollection as Collection,
    JMS\SerializerBundle\Annotation as JMS;
    
abstract class Entity
{

    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @JMS\Groups({"entity"})
     * @var integer
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime")
     * @JMS\Groups({"entity"})
     * @var datetime
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     * @JMS\Groups({"entity"})
     * @var datetime
     */
    protected $updated;

    public function __construct()
    {
        $this->setCreated(date('Y-m-d H:i:s'));
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function getCreated()
    {
        return $this->created;
    }
    
    public function setCreated($created)
    {
        $this->created = \DateTime::createFromFormat('Y-m-d H:i:s', $created);    
    }

    public function getUpdated()
    {
        return $this->updated;
    }
    
    public function setUpdated($updated)
    {
        $this->updated = \DateTime::createFromFormat('Y-m-d H:i:s', $updated);
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