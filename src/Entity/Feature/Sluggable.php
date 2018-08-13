<?php

namespace App\Entity\Feature;

interface Sluggable
{
    public function getSlug() : ?string;
}
