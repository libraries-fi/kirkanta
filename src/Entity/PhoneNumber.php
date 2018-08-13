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
    /**
     * @ORM\ManyToOne(targetEntity="Library", inversedBy="phone_numbers")
     */
    protected $library;

    /**
     * @ORM\ManyToOne(targetEntity="Department", inversedBy="phone_numbers")
     */
    protected $department;

    public function getNumber() : string
    {
        return $this->getContact();
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload) : void
    {
        if (preg_match('/[^\d ]/', $this->getNumber())) {
            $context->buildViolation('Only digits and spaces are allowed.')
                ->atPath('contact')
                ->addViolation();
        }
    }
}
