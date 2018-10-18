<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="addresses_data")
 */
class AddressData extends EntityDataBase
{
    /**
     * @ORM\Column(type="string")
     */
    private $street;

    /**
     * @ORM\Column(type="string")
     */
    private $area;

    /**
     * @ORM\Column(type="string")
     */
    private $info;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Address", inversedBy="translations")
     */
    protected $entity;

    public function getStreet() : ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street) : void
    {
        $this->street = $street;
    }

    public function getArea() : ?string
    {
        return $this->area;
    }

    public function setArea(?string $area) : void
    {
        $this->area = $area;
    }

    public function getInfo() : ?string
    {
        return $this->info;
    }

    public function setInfo(?string $info) : void
    {
        $this->info = $info;
    }

    public function getEntity() : Address
    {
        return $this->entity;
    }

    public function setEntity(Address $entity) : void
    {
        $this->entity = $entity;
    }
}
