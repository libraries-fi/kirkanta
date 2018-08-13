<?php

namespace App\Entity;

use App\I18n\Translations;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Organisation extends Facility
{
    /**
     * @ORM\Column(type="string")
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity="Library", mappedBy="parent")
     */
    private $libraries;

    /**
     * @ORM\ManyToOne(targetEntity="City", inversedBy="organisations")
     */
    private $city;

    /**
     * @ORM\OneToMany(targetEntity="OrganisationData", mappedBy="entity", orphanRemoval=true, cascade={"persist", "remove"}, fetch="EXTRA_LAZY", indexBy="langcode")
     */
    protected $translations;

    public function __construct()
    {
        parent::__construct();
        $this->libraries = new ArrayCollection;
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getName() : string
    {
        return $this->translations[$this->langcode]->getName();
    }

    public function setName(string $name) : void
    {
        $this->translations[$this->langcode]->setName($name);
    }

    public function getShortName() : ?string
    {
        return $this->translations[$this->langcode]->getShortName();
    }

    public function setShortName(?string $name) : void
    {
        $this->translations[$this->langcode]->setShortName($name);
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function setType(string $type) : void
    {
        $this->type = $type;
    }

    public function getLibraries() : Collection
    {
        return $this->libraries;
    }
}
