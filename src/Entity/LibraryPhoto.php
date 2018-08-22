<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Feature\Translatable;
use App\Entity\Library;
use App\I18n\Translations;

/**
 * @ORM\Entity
 */
class LibraryPhoto extends Picture implements Translatable
{
    use Feature\TranslatableTrait;

    static public $defaultSizes = ['small', 'medium', 'large', 'huge'];

    /**
     * @ORM\Column(type="string")
     */
    private $author;

    /**
     * @ORM\Column(type="string")
     */
    private $year;

    /**
     * @ORM\Column(type="boolean")
     */
    private $cover = false;

    /**
     * @ORM\OneToMany(targetEntity="LibraryPhotoData", mappedBy="entity", orphanRemoval=true, cascade={"persist", "remove"}, fetch="EXTRA_LAZY", indexBy="langcode")
     */
    private $translations;

    /**
     * @ORM\ManyToOne(targetEntity="Library", inversedBy="pictures")
     */
    private $parent;

    public function getParent() : Library
    {
        return $this->parent;
    }

    public function setParent(Library $organisation) : void
    {
        $this->parent = $organisation;
    }

    public function isDefault() : bool
    {
        return $this->cover == true;
    }

    public function setDefault(bool $state) : void
    {
        $this->cover = $state;
    }

    public function getName() : string
    {
        return $this->translations[$this->langcode]->getName();
    }

    public function setName(string $name) : void
    {
        $this->translations[$this->langcode]->setName($name);
    }

    public function getAuthor() : ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author) : void
    {
        $this->author = $author;
    }

    public function getDescription() : ?string
    {
        return $this->translations[$this->langcode]->getDescription();
    }

    public function setDescription(string $description) : void
    {
        $this->translations[$this->langcode]->setDescription($description);
    }

    public function getYear() : ?int
    {
        return $this->year;
    }

    public function setYear(int $year) : void
    {
        $this->year = $year;
    }
}
