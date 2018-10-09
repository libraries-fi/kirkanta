<?php

namespace App\Event;

use App\Entity\NestedImage;
use Symfony\Component\EventDispatcher\Event;
use Vich\UploaderBundle\Mapping\PropertyMapping;

class ImageUploadEvent extends Event
{
    private $image;
    private $mapping;

    public function __construct(NestedImage $image, PropertyMapping $mapping)
    {
        $this->image = $image;
        $this->mapping = $mapping;
    }

    public function getImage() : NestedImage
    {
        return $this->image;
    }

    public function getMapping() : PropertyMapping
    {
        return $this->mapping;
    }
}
