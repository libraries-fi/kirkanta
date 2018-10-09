<?php

namespace App\Entity\Feature;

use DateTime;

trait ModifiedAwarenessTrait
{
    use CreatedAwarenessTrait;

    /**
     * Timestamp for when the entity was created.
     *
     * @ORM\Column(type="datetime")
     */
    protected $modified;

    public function getModified() : DateTime
    {
        return $this->modified;
    }

    public function setModified(DateTime $modified = null) : void
    {
        $this->modified = $modified ?: new DateTime;
    }
}
