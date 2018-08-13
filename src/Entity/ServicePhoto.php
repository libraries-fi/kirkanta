<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Feature\Translatable;
use App\Entity\ServiceInstance;

/**
 * @ORM\Entity
 */
class ServicePhoto extends Picture
{
    /**
     * @ORM\ManyToOne(targetEntity="ServiceInstance", inversedBy="photos")
     */
    private $service;

    public function getService() : ServiceInstance
    {
        return $this->service;
    }
}
