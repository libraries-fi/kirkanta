<?php

namespace App\Entity\Feature;

use Doctrine\ORM\Mapping as ORM;

trait StateAwarenessTrait
{
    /**
     * @ORM\Column(type="integer")
     */
    protected $state = StateAwareness::DRAFT;

    public function getState() : int
    {
        return $this->state;
    }

    public function setState(int $state) : void
    {
        $this->state = $state;
    }

    public function isPublished() : bool
    {
        return $this->state == StateAwareness::PUBLISHED;
    }

    public function isDeleted() : bool
    {
        return $this->state == StateAwareness::DELETED;
    }
}
