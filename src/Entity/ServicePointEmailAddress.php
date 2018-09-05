<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ServicePointEmailAddress extends ContactInfo
{
    use EmailAddressTrait;

    /**
     * @ORM\ManyToOne(targetEntity="ServicePoint", inversedBy="email_addresses")
     */
    protected $parent;
}
