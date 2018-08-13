<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="provincial_libraries_data")
 */
class RegionalLibraryData extends EntityDataBase
{
    use Feature\SluggableDataTrait;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="string")
     */
    private $province;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="RegionalLibrary", inversedBy="translations")
     */
    protected $entity;

    public function getName() : string
    {
        return $this->name;
    }

    public function setName($name) : void
    {
        $this->name = $name;
    }

    public function getProvince() : string
    {
        return $this->province;
    }

    public function setProvince(string $province) : void
    {
        $this->province = $province;
    }

    public function getEntity() : RegionalLibrary
    {
        return $this->entity;
    }

    public function setEntity(RegionalLibrary $entity) : void
    {
        $this->entity = $entity;
    }
}
