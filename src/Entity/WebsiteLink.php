<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class WebsiteLink extends ContactInfo
{
    use ContactInfoTrait;
    use WebsiteLinkTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Facility", inversedBy="links")
     */
    protected $parent;

    /**
     * @ORM\ManyToOne(targetEntity="Department", inversedBy="links")
     */
    protected $department;
}
