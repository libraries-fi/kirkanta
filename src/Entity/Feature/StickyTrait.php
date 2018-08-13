<?php

namespace App\Entity\Feature;

trait StickyTrait
{
    /**
     * @ORM\Column(type="boolean")
     */
    private $sticky;

    public function isSticky() : bool
    {
        return $this->sticky ?? false;
    }

    public function setSticky(bool $state) : void
    {
        $this->sticky = $state;
    }
}
