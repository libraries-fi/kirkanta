<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ServicePointPhoneNumber extends ContactInfo
{
    use PhoneNumberTrait;

    /**
     * @ORM\ManyToOne(targetEntity="ServicePoint", inversedBy="phone_numbers")
     */
    protected $parent;
}
