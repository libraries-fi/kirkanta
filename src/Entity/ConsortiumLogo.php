<?php

namespace App\Entity;

use App\Entity\Feature\Translatable;
use App\Entity\Library;
use App\I18n\Translations;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity
 * @Vich\Uploadable
 */
class ConsortiumLogo extends Picture implements Translatable
{
    use Feature\TranslatableTrait;

    const DEFAULT_SIZES = ['small', 'medium'];

    /**
     * @ORM\Column(type="boolean")
     */
    private $cover = false;

    /**
     * @ORM\OneToMany(targetEntity="LibraryPhotoData", mappedBy="entity", orphanRemoval=true, cascade={"persist", "remove"}, fetch="EXTRA_LAZY", indexBy="langcode")
     */
    private $translations;

    /**
     * @ORM\OneToOne(targetEntity="Consortium", mappedBy="logo")
     */
    private $consortium;

    /**
     * @Vich\UploadableField(mapping="consortium_logo", fileNameProperty="filename", size="filesize", mimeType="mime_type", dimensions="dimensions", originalName="originalName")
     */
    protected $file;

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

    public function getConsortium() : Consortium
    {
        return $this->consortium;
    }

    public function setConsortium(Consortium $consortium) : void
    {
        $this->consortium = $consortium;
    }
}
