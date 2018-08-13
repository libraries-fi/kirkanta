<?php

namespace App\Entity\Feature;

use App\Entity\UserGroup;

interface GroupOwnership
{
    public function hasOwner() : bool;
    public function getOwner() : ?UserGroup;

    /**
     * @deprecated
     */
    public function getGroup() : ?UserGroup;
}
