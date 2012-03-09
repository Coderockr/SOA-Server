<?php
namespace model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints;
use DMS\Filter\Rules as Filter;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User extends Entity
{

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Filter\StripTags()
     * @Filter\Trim()
     * @Filter\StripNewlines()
     * @var string
     */
    private $login;
    
    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Filter\StripTags()
     * @Filter\Trim()
     * @Filter\StripNewlines()
     * @var string
     */
    private $password;

    public function getId()
    {
        return $this->id;
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setLogin($login)
    {
        $this->login = $login;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    static public function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('login', new Constraints\NotNull());
        $metadata->addPropertyConstraint('login', new Constraints\NotBlank());
        $metadata->addPropertyConstraint('password', new Constraints\MinLength(array('limit' => 4)));
    }
}
