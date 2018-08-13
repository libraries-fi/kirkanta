<?php

namespace App\Entity\Feature;

use Doctrine\ORM\Mapping as ORM;

trait WeightTrait
{
    /**
     * @ORM\Column(type="integer")
     */
    private $weight;

    public function getWeight() : int
    {
        return $this->weight ?? 0;
    }

    public function setWeight(int $weight) : void
    {
        $this->weight = $weight;
    }
}
