<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="service_instances_data")
 */
class ServiceInstanceData extends EntityDataBase
{
    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="string")
     */
    private $description;

    /**
     * @ORM\Column(type="string")
     */
    private $short_description;

    /**
     * Price definition for the item. To allow more verbose descriptions,
     * the type of this field is set to string.
     *
     * @ORM\Column(type="string")
     */
    private $price;

    /**
     * @ORM\Column(type="string")
     */
    private $website;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="ServiceInstance", inversedBy="translations")
     */
    protected $entity;

    public function getName() : ?string
    {
        return $this->name;
    }

    public function setName(?string $name) : void
    {
        $this->name = $name;
    }

    public function getDescription() : ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description) : void
    {
        $this->description = $description;
    }

    public function getShortDescription() : ?string
    {
        return $this->short_description;
    }

    public function setShortDescription(?string $description) : void
    {
        $this->short_description = $description;
    }

    public function getPrice() : ?string
    {
        return $this->price;
    }

    public function setPrice(?string $info) : void
    {
        $this->price = $info;
    }

    public function getWebsite() : ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $url) : void
    {
        $this->website = $url;
    }

    public function getEntity() : ServiceInstance
    {
        return $this->entity;
    }

    public function setEntity(ServiceInstance $entity) : void
    {
        $this->entity = $entity;
    }
}
