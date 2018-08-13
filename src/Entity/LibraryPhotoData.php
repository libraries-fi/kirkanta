<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Feature\Translatable;
use App\Entity\Library;
use App\I18n\Translations;

/**
 * @ORM\Entity
 * @ORM\Table(name="photos_data")
 */
class LibraryPhotoData extends PictureDataBase
{
    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="string")
     */
    private $description;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="LibraryPhoto", inversedBy="translations")
     */
    protected $entity;

    public function getName() : ?string
    {
        return $this->name;
    }

    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    public function getDescription() : ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description) : void
    {
        $this->description = $description;
    }

    public function getEntity() : LibraryPhoto
    {
        return $this->entity;
    }

    public function setEntity(LibraryPhoto $entity) : void
    {
        $this->entity = $entity;
    }
}
