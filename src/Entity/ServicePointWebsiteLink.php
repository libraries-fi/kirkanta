<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ServicePointWebsiteLink extends ContactInfo
{
    use WebsiteLinkTrait;

    /**
     * @ORM\ManyToOne(targetEntity="ServicePoint", inversedBy="links")
     */
    protected $parent;
}
