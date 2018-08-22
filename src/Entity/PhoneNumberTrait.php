<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

trait PhoneNumberTrait
{
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
