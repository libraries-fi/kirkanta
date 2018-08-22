<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Type for 'foreign service points' i.e. archives, museums and other non-libraries.
 *
 * @ORM\Entity
 */
class ServicePoint extends LibraryBase
{
    /**
     * @ORM\OneToMany(targetEntity="ServicePointPhoneNumber", mappedBy="parent", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $phone_numbers;

    /**
     * @ORM\OneToMany(targetEntity="Period", mappedBy="library", cascade={"persist", "remove"}, indexBy="id")
     * @ORM\OrderBy({"valid_from" = "desc", "valid_until" = "desc"})
     */
    protected $periods;

    /**
     * @ORM\OneToMany(targetEntity="LibraryPhoto", mappedBy="library", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $pictures;

    /**
     * @ORM\OneToMany(targetEntity="LibraryData", mappedBy="entity", orphanRemoval=true, cascade={"persist", "remove"}, fetch="EXTRA_LAZY", indexBy="langcode")
     */
    protected $translations;
}
