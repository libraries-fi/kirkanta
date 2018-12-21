<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
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

    public function __construct()
    {
        parent::__construct();

        $this->accessibility = new ArrayCollection;
        $this->periods = new ArrayCollection;
    }
}
