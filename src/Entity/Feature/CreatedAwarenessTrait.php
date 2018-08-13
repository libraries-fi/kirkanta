<?php

namespace App\Entity\Feature;

use DateTime;

trait CreatedAwarenessTrait
{
    /**
     * Timestamp for when the entity was created.
     *
     * @ORM\Column(type="datetime")
     */
    protected $created;

    public function getCreated() : DateTime
    {
        return $this->created;
    }

    public function setCreated(DateTime $created) : void
    {
        $this->created = $created;
    }
}
