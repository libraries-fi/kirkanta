<?php

namespace App\Entity;

use App\I18n\Translations;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class MobileStop extends Facility
{
    /**
     * @ORM\ManyToOne(targetEntity="City", inversedBy="organisations")
     */
    private $city;

    /**
     * @ORM\OneToOne(targetEntity="Address", cascade={"remove", "persist"}, orphanRemoval=true)
     */
    private $address;

    /**
     * @ORM\OneToMany(targetEntity="OrganisationData", mappedBy="entity", orphanRemoval=true, cascade={"persist", "remove"}, fetch="EXTRA_LAZY", indexBy="langcode")
     */
    protected $translations;

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

    public function getAddress() : ?Address
    {
        return $this->address;
    }

    public function setAddress(Address $address) : void
    {
        $this->address = $address;
        $this->setCity($address->getCity());
    }
}
