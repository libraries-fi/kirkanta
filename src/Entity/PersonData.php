<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="persons_data")
 */
class PersonData extends EntityDataBase
{
    /**
     * @ORM\Column(type="string", length=60)
     */
    private $job_title;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $responsibility;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Person", inversedBy="translations")
     */
    protected $entity;

    public function getJobTitle() : ?string
    {
        return $this->job_title;
    }

    public function setJobTitle(string $title) : void
    {
        $this->job_title = $title;
    }

    public function getResponsibility() : ?string
    {
        return $this->responsibility;
    }

    public function setResponsibility(?string $responsibility) : void
    {
        $this->responsibility = $responsibility;
    }

    public function getEntity() : Person
    {
        return $this->entity;
    }

    public function setEntity(Person $entity) : void
    {
        $this->entity = $entity;
    }
}
