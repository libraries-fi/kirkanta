<?php

namespace App\Entity\Feature;

use Doctrine\ORM\Mapping as ORM;

/**
 * NOTE: Use SluggableDataTrait with the Data Entity!
 */
trait SluggableTrait
{
    public function getSlug() : string
    {
        return $this->translations[$this->langcode]->getSlug();
    }

    public function setSlug(string $slug) : void
    {
        $this->translations[$this->langcode]->setSlug($slug);
    }
}
