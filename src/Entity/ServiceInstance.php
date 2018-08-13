<?php

namespace App\Entity;

use DateTime;
use Serializable;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Feature\CreatedAwareness;
use App\Entity\Feature\GroupOwnership;
use App\Entity\Feature\Translatable;
use App\I18n\Translations;

/**
 * @ORM\Entity
 * @ORM\Table(name="service_instances")
 */
class ServiceInstance extends EntityBase implements CreatedAwareness, GroupOwnership, Translatable
{
    use Feature\CreatedAwarenessTrait;
    use Feature\GroupOwnershipTrait;
    use Feature\TranslatableTrait;

    /**
     * Basename of the picture file
     *
     * @ORM\Column(type="string")
     */
    private $picture;

    /**
     * @ORM\Column(type="boolean")
     */
    private $for_loan = false;

    /**
     * @ORM\ManyToOne(targetEntity="Service", inversedBy="instances", fetch="EAGER")
     */
    private $template;

    /**
     * @ORM\ManyToOne(targetEntity="Library", inversedBy="services")
     */
    private $library;

    /**
     * @ORM\Column(type="string")
     */
    private $phone_number;

    /**
     * @ORM\Column(type="string")
     */
    private $email;

    /**
     * @ORM\Column(type="integer")
     */
    private $helmet_priority;

    /**
     * @ORM\Column(type="boolean")
     */
    private $shared = false;

    /**
     * @ORM\OneToMany(targetEntity="ServiceInstanceData", mappedBy="entity", orphanRemoval=true, cascade={"persist", "remove"}, fetch="EXTRA_LAZY", indexBy="langcode")
     */
    private $translations;

    public function getStandardName() : string
    {
        return $this->getTemplate()->getName();
    }

    public function getType() : string
    {
        return $this->getTemplate()->getType();
    }

    public function isShared() : bool
    {
        return $this->shared == true;
    }

    public function getName() : ?string
    {
        return $this->translations[$this->langcode]->getName();
    }

    public function setName(?string $name) : void
    {
        $this->translations[$this->langcode]->setName($name);
    }

    public function getDescription() : ?string
    {
        return $this->translations[$this->langcode]->getDescription();
    }

    public function setDescription(?string $description) : void
    {
        $this->translations[$this->langcode]->setDescription($description);
    }

    public function getShortDescription() : ?string
    {
        return $this->translations[$this->langcode]->getShortDescription();
    }

    public function setShortDescription(?string $description) : void
    {
        $this->translations[$this->langcode]->setShortDescription($description);
    }

    public function getPrice() : ?string
    {
        return $this->translations[$this->langcode]->getPrice();
    }

    public function setPrice(?string $info) : void
    {
        $this->translations[$this->langcode]->setPrice($info);
    }

    public function isForLoan() : bool
    {
        return $this->for_loan == true;
    }

    public function setForLoan(bool $state) : void
    {
        $this->for_loan = $state;
    }

    public function getEmail() : ?string
    {
        return $this->email;
    }

    public function setEmail(string $email) : void
    {
        $this->email = $email;
    }

    public function getPhoneNumber() : ?string
    {
        return $this->phone_number;
    }

    public function setPhoneNumber(string $number) : void
    {
        $this->phone_number = $number;
    }

    public function getWebsite() : ?string
    {
        return $this->translations[$this->langcode]->getWebsite();
    }

    public function setWebsite(?string $url) : void
    {
        $this->translations[$this->langcode]->setWebsite($url);
    }

    public function getTemplate() : Service
    {
        return $this->template;
    }

    public function setTemplate(Service $service) : void
    {
        $this->template = $service;
    }

    public function getPicture() : ?string
    {
        return $this->picture;
    }

    public function setPicture(string $picture = null) : void
    {
        $this->picture = $picture;
    }

    /**
     * No library set if instance is a shared template.
     */
    public function getLibrary() : ?Library
    {
        return $this->library;
    }

    public function setLibrary(Library $library) : void
    {
        $this->library = $library;
        $this->library->getServices()->add($this);
    }
}
