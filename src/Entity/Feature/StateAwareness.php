<?php

namespace App\Entity\Feature;

interface StateAwareness
{
    const DELETED = -1;
    const DRAFT = 0;
    const PUBLISHED = 1;

    public function getState() : int;
    public function isPublished() : bool;
    public function isDeleted() : bool;
}
