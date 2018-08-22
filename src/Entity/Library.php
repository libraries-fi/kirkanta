<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Library extends LibraryBase
{
    /**
     * @ORM\OneToMany(targetEntity="PhoneNumber", mappedBy="parent", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"weight": "ASC", "id": "ASC"})
     */
    protected $phone_numbers;

    /**
     * @ORM\OneToMany(targetEntity="WebsiteLink", mappedBy="parent", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"weight": "ASC", "id": "ASC"})
     */
    protected $links;

    /**
     * @ORM\OneToMany(targetEntity="LibraryPhoto", mappedBy="parent", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"weight": "ASC", "id": "ASC"})
     */
    protected $pictures;

    /**
     * @ORM\OneToMany(targetEntity="Person", mappedBy="library", cascade={"persist", "remove"})
     */
    protected $persons;

    /**
     * @ORM\OneToMany(targetEntity="Period", mappedBy="parent", cascade={"persist", "remove"}, indexBy="id")
     * @ORM\OrderBy({"valid_from" = "desc", "valid_until" = "desc"})
     */
    protected $periods;

    /**
     * @ORM\OneToMany(targetEntity="ServiceInstance", mappedBy="parent", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $services;

    /**
     * @ORM\OneToMany(targetEntity="Department", mappedBy="parent", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $departments;

    public function __construct()
    {
        parent::__construct();
        $this->services = new ArrayCollection;
        $this->departments = new ArrayCollection;
    }

    public function getDepartments() : Collection
    {
        return $this->departments;
    }

    public function getServices() : Collection
    {
        return $this->services;
    }

    public function getLinks() : Collection
    {
        return $this->links;
    }
}
