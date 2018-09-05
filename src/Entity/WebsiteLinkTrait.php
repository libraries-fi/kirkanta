<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

trait WebsiteLinkTrait
{
    public function getUrl() : string
    {
        return $this->getContact();
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload) : void
    {
        if (!preg_match('/^http(s?):\/\//', $this->getUrl())) {
            $context->buildViolation('The URL has to start with "http://" or "https://"')
                ->atPath('contact')
                ->addViolation();
        }
    }
}
