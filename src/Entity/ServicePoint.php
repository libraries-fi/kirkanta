<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ServicePoint extends LibraryBase
{
    /**
     * @ORM\OneToMany(targetEntity="ServicePointPhoneNumber", mappedBy="parent", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $phone_numbers;

    /**
     * @ORM\OneToMany(targetEntity="LibraryData", mappedBy="entity", orphanRemoval=true, cascade={"persist", "remove"}, fetch="EXTRA_LAZY", indexBy="langcode")
     */
    protected $translations;
}
