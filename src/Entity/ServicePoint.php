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
     * @ORM\OneToMany(targetEntity="ServicePointPhoneNumber", mappedBy="parent", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $phone_numbers;

    /**
     * @ORM\OneToMany(targetEntity="ServicePointEmailAddress", mappedBy="parent", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $email_addresses;

    /**
     * @ORM\OneToMany(targetEntity="ServicePointWebsiteLink", mappedBy="parent", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $links;

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
        $this->mobile_stops = new ArrayCollection;
        $this->periods = new ArrayCollection;
        $this->phone_numbers = new ArrayCollection;
        $this->pictures = new ArrayCollection;
    }
}
