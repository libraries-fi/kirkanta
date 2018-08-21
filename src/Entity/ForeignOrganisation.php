<?php

namespace App\Entity;

use App\I18n\Translations;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ForeignOrganisation extends Facility
{
    /**
     * @ORM\Column(type="string")
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="City", inversedBy="organisations")
     */
    private $city;

    /**
     * @ORM\OneToOne(targetEntity="Address", cascade={"remove", "persist"}, orphanRemoval=true)
     */
    private $address;

    /**
     * @ORM\OneToOne(targetEntity="Address", cascade={"remove", "persist"}, orphanRemoval=true)
     */
    private $mail_address;

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

    public function getAddress() : ?Address
    {
        return $this->address;
    }

    public function setAddress(Address $address) : void
    {
        $this->address = $address;
        $this->setCity($address->getCity());
    }

    public function getMailAddress() : ?Address
    {
        return $this->mail_address;
    }

    public function setMailAddress(?Address $address) : void
    {
        $this->mail_address = $address;
    }
}
