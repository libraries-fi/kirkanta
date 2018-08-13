<?php

namespace App\Entity\Feature;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\UserGroup;

trait GroupOwnershipTrait
{
    /**
     * NOTE: Use FQCN so that this trait can be imported in other bundles.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\UserGroup")
     */
    protected $group;

    public function hasOwner() : bool
    {
        return $this->group != null;
    }

    public function getOwner() : ?UserGroup
    {
        return $this->group;
    }

    public function setOwner(UserGroup $owner) : void
    {
        $this->group = $owner;
    }

    public function getGroup() : ?UserGroup
    {
        return $this->getOwner();
    }
}
