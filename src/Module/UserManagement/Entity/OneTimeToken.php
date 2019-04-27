<?php

namespace App\Module\UserManagement\Entity;

use App\Entity\Feature;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use UserAccountsBundle\UserInterface;

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

    private $nonce;

    public static function nonce() : string
    {
        return bin2hex(random_bytes(20));
    }

    public function __construct(string $purpose, string $nonce = null)
    {
        $this->created = new DateTime();
        $this->nonce = $nonce ?? self::nonce();
        $this->token = hash('sha256', $this->nonce);
        $this->purpose = $purpose;
    }

    public function getNonce() : ?string
    {
        return $this->nonce;
    }

    public function getToken() : string
    {
        return $this->token;
    }

    public function getPurpose() : string
    {
        return $this->purpose;
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
