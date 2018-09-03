<?php

namespace App\Module\UserManagement\Entity;

use App\Entity\Feature;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Module\UserManagement\Doctrine\OneTimeTokenRepository")
 * @ORM\Table(name="one_time_tokens")
 */
class OneTimeToken
{
    use Feature\CreatedAwarenessTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    protected $token;

    /**
     * @ORM\Column(type="string")
     */
    private $purpose;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    protected $user;

    public function __construct(string $token = null, string $purpose = null)
    {
        $this->created = new DateTime;

        if ($token) {
            $this->setToken($token);
        }

        if ($purpose) {
            $this->setPurpose($purpose);
        }
    }

    public function getToken() : string
    {
        return $this->token;
    }

    public function setToken(string $token) : void
    {
        $this->token = $token;
    }

    public function getPurpose() : string
    {
        return $this->purpose;
    }

    public function setPurpose(string $purpose) : void
    {
        $this->purpose = $purpose;
    }

    public function getUser() : ?UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user) : void
    {
        $this->user = $user;
    }
}
