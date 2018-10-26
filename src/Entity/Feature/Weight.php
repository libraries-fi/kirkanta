<?php

namespace App\Entity\Feature;

interface Weight
{
    public function getWeight() : ?int;
    public function setWeight(int $weight) : void;
}
