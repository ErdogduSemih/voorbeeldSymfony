<?php

namespace App\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table("user")
 * @ORM\Entity(repositoryClass="UserRepository")
 */
class User implements UserInterface, \Serializable
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="userName", type="string", length=20)
     * @Assert\NotBlank()
     * @Assert\Length(min=3)
     * @Assert\Regex("/[^A-Za-z]/",
     *                  match=false,
     *                  message="Username can only contain letters"
     *              )
     */
    private $userName;

    /**
     * @ORM\Column(name="password", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(min=3)
     */
    private $password;

    /**
     * @ORM\Column(name="rolesString", type="string", length=255)
     */
    private $rolesString;

    public function getUserName()
    {
        return $this->userName;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function eraseCredentials()
    {
    }

    public function getRoles()
    {
        return preg_split("/[\s,]+/", $this->rolesString);
    }

    public function getSalt()
    {
        return null;
    }

    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->userName,
            $this->password,
            $this->rolesString
        ));
    }

    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->userName,
            $this->password,
            $this->rolesString
            ) = unserialize($serialized);
    }

    public function getId()
    {
        return $this->id;
    }

    public function setUserName($userName)
    {
        $this->userName = $userName;

        return $this;
    }

    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    public function setRolesString($rolesString)
    {
        $this->rolesString = $rolesString;

        return $this;
    }

    public function getRolesString()
    {
        return $this->rolesString;
    }

    public function __toString()
    {
        return "Entity User, username= " . $this->userName;
    }
}
