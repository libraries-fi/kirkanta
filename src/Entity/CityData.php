<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="cities_data")
 */
class CityData extends EntityDataBase
{
    use Feature\SluggableDataTrait;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="City", inversedBy="translations")
     */
    protected $entity;

    public function getName() : ?string
    {
        return $this->name;
    }

    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    public function getEntity() : City
    {
        return $this->entity;
    }

    public function setEntity(City $entity) : void
    {
        $this->entity = $entity;
    }
}
