<?php

namespace App\Module\ServiceTree\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\EntityBase;
use App\Entity\Service;

/**
 * @ORM\Entity
 * @ORM\Table(name="service_tree_items")
 */
class ServiceItem extends EntityBase
{
    /**
     * @ORM\ManyToOne(targetEntity="ServiceCategory", inversedBy="items")
     */
    protected $category;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Service")
     */
    protected $service;

    public function __construct(?ServiceCategory $category, ?Service $service)
    {
        $this->category = $category;
        $this->service = $service;
    }

    public function __toString()
    {
        return $this->service->getName();
    }

    public function getName() : string
    {
        return $this->service->getName();
    }

    public function getCategory() : ServiceCategory
    {
        return $this->category;
    }

    public function setCategory(ServiceCategory $category) : void
    {
        $this->category = $category;
    }

    public function getService() : Service
    {
        return $this->service;
    }

    public function setService(Service $service) : void
    {
        $this->service = $service;
    }
}
