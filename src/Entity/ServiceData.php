<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="services_data")
 */
class ServiceData extends EntityDataBase
{
    use Feature\SluggableDataTrait;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $name;

    /**
     * @ORM\Column(type="string")
     */
    private $description;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Service", inversedBy="translations")
     */
    protected $entity;

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name) : void
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

    public function getEntity() : Service
    {
        return $this->entity;
    }

    public function setEntity(Service $entity) : void
    {
        $this->entity = $entity;
    }
}
