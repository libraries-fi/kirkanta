<?php

namespace App\Entity\Feature;

use DateTime;

interface CreatedAwareness
{
    public function getCreated() : DateTime;
}
