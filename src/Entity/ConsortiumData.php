<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="consortiums_data")
 */
class ConsortiumData extends EntityDataBase
{
    use Feature\SluggableDataTrait;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="string")
     */
    private $homepage;

    /**
     * @ORM\Column(type="string")
     */
    private $description;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Consortium", inversedBy="translations")
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

    public function getHomepage() : ?string
    {
        return $this->homepage;
    }

    public function setHomepage(?string $url) : void
    {
        $this->homepage = $url;
    }

    public function getDescription() : ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description) : void
    {
        $this->description = $description;
    }

    public function getEntity() : Consortium
    {
        return $this->entity;
    }

    public function setEntity(Consortium $entity) : void
    {
        $this->entity = $entity;
    }
}
