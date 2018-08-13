<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="regions_data")
 */
class RegionData extends EntityDataBase
{
    use Feature\SluggableDataTrait;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Region", inversedBy="translations")
     */
    protected $entity;

    public function getName() :string
    {
        // return $this->name;
        return $this->name;
    }

    public function setName($name) : void
    {
        $this->name = $name;
    }

    public function getEntity() : Region
    {
        return $this->entity;
    }

    public function setEntity(Region $entity) : void
    {
        $this->entity = $entity;
    }
}
