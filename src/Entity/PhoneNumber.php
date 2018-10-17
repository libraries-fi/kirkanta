<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class PhoneNumber extends ContactInfo
{
    use ContactInfoTrait;
    use PhoneNumberTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Facility", inversedBy="phone_numbers")
     */
    protected $parent;

    /**
     * @ORM\ManyToOne(targetEntity="Department", inversedBy="phone_numbers")
     */
    protected $department;
}
