<?php

namespace App\Entity;

use App\Entity\Feature\Sluggable;
use App\Entity\Feature\Translatable;
use App\I18n\Translations;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="cities")
 */
class City extends EntityBase implements Sluggable, Translatable
{
    use Feature\SluggableTrait;
    use Feature\TranslatableTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Region", inversedBy="cities")
     */
    private $region;

    /**
     * @ORM\ManyToOne(targetEntity="Consortium", inversedBy="cities")
     */
    private $consortium;

    /**
     * @ORM\ManyToOne(targetEntity="RegionalLibrary")
     */
    private $regional_library;

    /**
     * @ORM\OneToMany(targetEntity="CityData", mappedBy="entity", orphanRemoval=true, cascade={"persist", "remove"}, fetch="EXTRA_LAZY", indexBy="langcode")
     */
    private $translations;

    /**
     * @ORM\OneToMany(targetEntity="Library",  mappedBy="city")
     */
    private $libraries;

    /**
     * @ORM\OneToMany(targetEntity="Organisation",  mappedBy="city")
     */
    private $organisations;

    public function __construct()
    {
        parent::__construct();
        $this->libraries = new ArrayCollection();
        $this->organisations = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getName() : string
    {
        if (!isset($this->translations[$this->langcode])) {
            var_dump($this->id);
        }
        return $this->translations[$this->langcode]->getName();
    }

    public function setName($name) : void
    {
        $this->translations[$this->langcode]->setName($name);
    }

    public function getRegion() : ?Region
    {
        return $this->region;
    }

    public function setRegion(Region $region) : void
    {
        $this->region = $region;
    }

    public function getRegionalLibrary() : ?RegionalLibrary
    {
        return $this->regional_library;
    }

    public function setRegionalLibrary(RegionalLibrary $library) : void
    {
        $this->regional_library = $library;
    }

    public function getConsortium() : ?Consortium
    {
        return $this->consortium;
    }

    public function setConsortium(?Consortium $consortium) : void
    {
        $this->consortium = $consortium;
    }

    public function getLibraries() : Collection
    {
        return $this->libraries;
    }

    public function getOrganisations() : Collection
    {
        return $this->organisations;
    }
}
