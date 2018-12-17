<?php

namespace UserAccountsBundle;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @ORM\Column(type="text[]")
     */
    protected $roles = [];

    /**
     * @ORM\Column(type="datetime")
     */
    protected $expires;

    /**
     * @Assert\Length(min = 8, max = 100)
     */
    private $raw_password;

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

    public function getEmail() : ?string
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
        return $this->roles ?? [];
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
        $this->raw_password = null;
    }

    public function getRawPassword() : ?string
    {
        return $this->raw_password;
    }

    public function setRawPassword(?string $password) : void
    {
        $this->raw_password = $password;
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

    public function getExpires() : ?DateTimeInterface
    {
        return $this->expires;
    }

    public function setExpires(?DateTimeInterface $date) : void
    {
        $this->expires = $date;
    }

    public function isAccountNonExpired() : bool
    {
        return !$this->expires || $this->expires > new DateTime;
    }

    public function isAccountNonLocked() : bool
    {
        return $this->isAccountNonExpired();
    }

    public function isCredentialsNonExpired() : bool
    {
        return $this->isAccountNonExpired();
    }

    public function isEnabled() : bool
    {
        return $this->isAccountNonExpired();
    }
}
