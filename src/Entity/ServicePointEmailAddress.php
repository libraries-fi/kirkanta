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

    public function getParent() : ServicePoint
    {
        return $this->parent;
    }

    public function setParent(ServicePoint $service_point) : void
    {
        $this->parent = $service_point;
    }
}
