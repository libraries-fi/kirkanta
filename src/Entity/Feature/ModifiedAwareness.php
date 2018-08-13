<?php

namespace App\Entity\Feature;

use DateTime;

interface ModifiedAwareness extends CreatedAwareness
{
    public function getModified() : DateTime;
}
