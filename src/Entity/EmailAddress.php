<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity
 */
class EmailAddress extends ContactInfo
{
    use ContactInfoTrait;
    use EmailAddressTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Department", inversedBy="phone_numbers")
     */
    protected $department;

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload) : void
    {
        if (!preg_match('/^[\w\.]+@[a-z\.]+$/', $this->getEmail())) {
            $context->buildViolation('Not a valid email address')
                ->atPath('contact')
                ->addViolation();
        }
    }
}
