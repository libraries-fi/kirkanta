<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="departments_data")
 */
class DepartmentData extends EntityDataBase
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
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Department", inversedBy="translations")
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

    public function getEntity() : Department
    {
        return $this->entity;
    }

    public function setEntity(Department $entity) : void
    {
        $this->entity = $entity;
    }
}
