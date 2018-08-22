<?php

namespace App\Entity;

use App\Entity\Feature\Translatable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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
