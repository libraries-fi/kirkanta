<?php

namespace App\Entity;

trait EmailAddressTrait
{
    public function getEmail() : string
    {
        return $this->getContact();
    }
}
