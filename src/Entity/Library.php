<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Library extends LibraryBase
{
    /**
     * @ORM\OneToMany(targetEntity="PhoneNumber", mappedBy="parent", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $phone_numbers;

    /**
     * @ORM\OneToMany(targetEntity="Person", mappedBy="library", cascade={"persist", "remove"})
     */
    protected $persons;

    /**
     * @ORM\OneToMany(targetEntity="Period", mappedBy="library", cascade={"persist", "remove"}, indexBy="id")
     * @ORM\OrderBy({"valid_from" = "desc", "valid_until" = "desc"})
     */
    protected $periods;

    /**
     * @ORM\OneToMany(targetEntity="ServiceInstance", mappedBy="library", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $services;

    /**
     * @ORM\OneToMany(targetEntity="LibraryPhoto", mappedBy="library", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $pictures;

    /**
     * @ORM\OneToMany(targetEntity="Department", mappedBy="parent", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $departments;

    /**
     * @ORM\OneToMany(targetEntity="LibraryData", mappedBy="entity", orphanRemoval=true, cascade={"persist", "remove"}, fetch="EXTRA_LAZY", indexBy="langcode")
     */
    protected $translations;
}
