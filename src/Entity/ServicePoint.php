<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Type for 'foreign service points' i.e. archives, museums and other non-libraries.
 *
 * @ORM\Entity
 */
class ServicePoint extends Facility implements LibraryInterface
{
    use LibraryTrait;

    /**
     * @ORM\OneToMany(targetEntity="Period", mappedBy="parent", cascade={"persist", "remove"}, indexBy="id")
     * @ORM\OrderBy({"valid_from" = "desc", "valid_until" = "desc"})
     */
    protected $periods;

    /**
     * @ORM\OneToMany(targetEntity="LibraryPhoto", mappedBy="parent", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $pictures;

    public function __construct()
    {
        $this->accessibility = new ArrayCollection;
        $this->periods = new ArrayCollection;
        $this->pictures = new ArrayCollection;
    }
}
