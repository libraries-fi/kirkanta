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
     * @ORM\ManyToOne(targetEntity="Library", inversedBy="links")
     */
    protected $parent;

    /**
     * @ORM\ManyToOne(targetEntity="Department", inversedBy="links")
     */
    protected $department;
}
