<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Feature\GroupOwnership;
use App\Entity\Feature\ModifiedAwareness;
use App\Entity\Feature\Sluggable;
use App\Entity\Feature\StateAwareness;
use App\Entity\Feature\Translatable;
use App\I18n\Translations;

/**
 * @ORM\Entity
 * @ORM\Table(name="organisations_data")
 */
class LibraryData extends EntityDataBase
{
    use Feature\SluggableDataTrait;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="string")
     */
    private $short_name;

    /**
     * @ORM\Column(type="string")
     */
    private $description;

    /**
     * @ORM\Column(type="string")
     */
    private $slogan;

    /**
     * @ORM\Column(type="string")
     */
    private $homepage;

    /**
     * @ORM\Column(type="string")
     */
    private $email;

    /**
     * @ORM\Column(type="string")
     */
    private $transit_directions;

    /**
     * @ORM\Column(type="string")
     */
    private $parking_instructions;

    /**
     * @ORM\Column(type="string")
     */
    private $building_name;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="LibraryBase", inversedBy="translations")
     */
    protected $entity;

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getShortName() : ?string
    {
        return $this->short_name;
    }

    public function setShortName(?string $name) : void
    {
        $this->short_name = $name;
    }

    public function getSlogan() : ?string
    {
        return $this->slogan;
    }

    public function setSlogan(?string $slogan) : void
    {
        $this->slogan = $slogan;
    }

    public function getDescription() : ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description) : void
    {
        $this->description = $description;
    }

    public function getTransitDirections() : ?string
    {
        return $this->transit_directions;
    }

    public function setTransitDirections(?string $info) : void
    {
        $this->transit_directions = $info;
    }

    public function getParkingInstructions() : ?string
    {
        return $this->parking_instructions;
    }

    public function setParkingInstructions(?string $info) : void
    {
        $this->parking_instructions = $info;
    }

    public function getEmail() : string
    {
        return $this->email;
    }

    public function setEmail(string $email) : void
    {
        $this->email = $email;
    }

    public function getHomepage() : ?string
    {
        return $this->homepage;
    }

    public function setHomepage(?string $homepage) : void
    {
        $this->homepage = $homepage;
    }

    public function getBuildingName() : ?string
    {
        return $this->building_name;
    }

    public function setBuildingName(?string $name) : void
    {
        $this->building_name = $name;
    }

    public function getEntity() : Facility
    {
        return $this->entity;
    }

    public function setEntity(Facility $entity) : void
    {
        $this->entity = $entity;
    }
}
