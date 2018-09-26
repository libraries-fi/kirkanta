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

    public function getParent() : ServicePoint
    {
        return $this->parent;
    }

    public function setParent(ServicePoint $service_point) : void
    {
        $this->parent = $service_point;
    }
}
