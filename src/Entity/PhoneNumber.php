<?php

namespace App\Entity;

use App\Entity\Feature\Translatable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class PhoneNumber extends ContactInfo
{
    use PhoneNumberTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Library", inversedBy="phone_numbers")
     */
    protected $parent;
}
