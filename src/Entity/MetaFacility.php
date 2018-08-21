<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class MetaFacility extends Facility
{
    /**
     * @ORM\Column(type="string")
     */
    private $type;

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

    public function getType() : string
    {
        return $this->type;
    }

    public function setType(string $type) : void
    {
        $this->type = $type;
    }
}
