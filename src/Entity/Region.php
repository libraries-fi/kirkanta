<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Feature\Sluggable;
use App\Entity\Feature\Translatable;
use App\I18n\Translations;

/**
 * @ORM\Entity
 * @ORM\Table(name="regions")
 */
class Region extends EntityBase implements Sluggable, Translatable
{
    use Feature\SluggableTrait;
    use Feature\TranslatableTrait;

    /**
     * @ORM\OneToMany(targetEntity="RegionData", mappedBy="entity", orphanRemoval=true, cascade={"persist", "remove"}, fetch="EXTRA_LAZY", indexBy="langcode")
     */
    private $translations;

    /**
     * @ORM\OneToMany(targetEntity="City", mappedBy="region")
     */
    private $cities;

    public function __construct()
    {
        parent::__construct();
        $this->cities = new ArrayCollection;
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

    public function getCities() : ArrayCollection
    {
        return $this->cities;
    }
}
