<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Feature\CreatedAwareness;

/**
 * @ORM\Entity(repositoryClass="App\Doctrine\NotificationRepository")
 * @ORM\Table(name="notifications")
 */
class Notification extends EntityBase implements CreatedAwareness
{
    use Feature\CreatedAwarenessTrait;

    /**
     * @ORM\Column(type="string")
     */
    private $subject;

    /**
     * @ORM\Column(type="string")
     */
    private $message;

    public function getSubject() : string
    {
        return $this->subject;
    }

    public function setSubject(string $subject) : void
    {
        $this->subject = $subject;
    }

    public function getMessage() : string
    {
        return $this->message;
    }

    public function setMessage(string $message) : void
    {
        $this->message = $message;
    }
}
