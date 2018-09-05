<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

trait EmailAddressTrait
{
    public function getEmail() : string
    {
        return $this->getContact();
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload) : void
    {
        if (!preg_match('/^[\w\.]@[a-z\.]+$/', $this->getEmail())) {
            $context->buildViolation('Not a valid email address')
                ->atPath('contact')
                ->addViolation();
        }
    }
}
