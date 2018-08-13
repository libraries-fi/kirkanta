<?php

namespace UserAccountsBundle;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

trait BasicUserImplementation
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    protected $email;

    /**
     * @ORM\Column(type="string", length=40, unique=true)
     */
    protected $username;

    /**
     * @ORM\Column(type="string", length=60)
     */
    protected $password;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $last_login;

    /**
     * @ORM\Column(type="json_array")
     */
    protected $roles = [];

    public function getId() : int
    {
        return $this->id;
    }

    public function getUsername() : string
    {
        return $this->username;
    }

    public function setUsername(string $username) : void
    {
        $this->username = $username;
    }

    public function getPassword() : ?string
    {
        return $this->password;
    }

    public function setPassword(string $hash) : void
    {
        $this->password = $hash;
    }

    public function getEmail() : string
    {
        return $this->email;
    }

    public function setEmail(string $email) : void
    {
        $this->email = $email;
    }

    public function getLastLogin() : ?DateTime
    {
        return $this->last_login;
    }

    public function setLastLogin(DateTimeInterface $time)
    {
        $this->last_login = $time;
    }

    public function getRoles() : array
    {
        return $this->roles;
    }

    public function setRoles(array $roles) : void
    {
        $this->roles = $roles;
    }

    /**
     * NOTE: This method is meant for clearing any temporary sensitive data like plaintext password.
     */
    public function eraseCredentials() : void
    {
        // Pass
    }

    public function serialize() : string
    {
        return serialize([$this->id, $this->username, $this->password]);
    }

    public function unserialize($data) : void
    {
        list($this->id, $this->username, $this->password) = unserialize($data);
    }

    public function getSalt() : ?string
    {
        return null;
    }
}
