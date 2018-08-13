<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="periods_data")
 */
class PeriodData extends EntityDataBase
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
     * @ORM\ManyToOne(targetEntity="Period", inversedBy="translations")
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

    public function getEntity() : Period
    {
        return $this->entity;
    }

    public function setEntity(Period $entity) : void
    {
        $this->entity = $entity;
    }
}
