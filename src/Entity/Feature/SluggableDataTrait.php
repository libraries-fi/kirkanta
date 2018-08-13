<?php

namespace App\Entity\Feature;

use Doctrine\ORM\Mapping as ORM;

trait SluggableDataTrait
{
    /**
     * @ORM\Column(type="string")
     */
    protected $slug;

    public function getSlug() : ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug) : void
    {
        $this->slug = $slug;
    }
}
