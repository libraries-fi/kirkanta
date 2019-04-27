<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\EntityListeners({"App\Doctrine\EventListener\LibraryConsortiumInitializer"})
 */
class Library extends Facility implements LibraryInterface
{
    use LibraryTrait;

    /**
     * @ORM\OneToMany(targetEntity="Person", mappedBy="library", cascade={"persist", "remove"})
     */
    protected $persons;

    /**
     * @ORM\OneToMany(targetEntity="ServiceInstance", mappedBy="parent", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $services;

    /**
     * @ORM\OneToMany(targetEntity="Department", mappedBy="parent", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $departments;

    /**
     * @ORM\ManyToOne(targetEntity="Organisation", inversedBy="libraries")
     */
    private $organisation;

    /**
     * @ORM\Column(type="boolean")
     */
    private $main_library = false;

    public function __construct()
    {
        parent::__construct();

        $this->services = new ArrayCollection();
        $this->departments = new ArrayCollection();
        $this->persons = new ArrayCollection();

        $this->accessibility = new ArrayCollection();
        $this->mobile_stops = new ArrayCollection();
        $this->periods = new ArrayCollection();
    }

    public function getDepartments() : Collection
    {
        return $this->departments;
    }

    public function getPersons() : Collection
    {
        return $this->persons;
    }

    public function getServices() : Collection
    {
        return $this->services;
    }

    public function getOrganisation() : ?Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(?Organisation $parent) : void
    {
        $this->organisation = $parent;
    }

    public function isMainLibrary() : bool
    {
        return $this->main_library;
    }

    public function setMainLibrary(bool $state) : void
    {
        $this->main_library = $state;
    }

    public function setDefaultLangcode(string $langcode) : void
    {
        parent::setDefaultLangcode($langcode);

        if ($addr = $this->getAddress()) {
            $addr->setDefaultLangcode($langcode);
        }

        if ($addr = $this->getMailAddress()) {
            $addr->setDefaultLangcode($langcode);
        }
    }
}
