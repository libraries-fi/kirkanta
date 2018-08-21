<?php

namespace App\Entity;

use App\Entity\Feature\GroupOwnership;
use App\Entity\Feature\ModifiedAwareness;
use App\Entity\Feature\Sluggable;
use App\Entity\Feature\StateAwareness;
use App\Entity\Feature\Translatable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="organisations")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="role", type="string")
 * @ORM\DiscriminatorMap({
 *     "department "= "Department",
 *     "foreign" = "ForeignOrganisation",
 *     "library" = "Library",
 *     "meta" = "MetaFacility",
 *     "mobile_stop" = "MobileStop",
 *     "organisation" = "Organisation",
 * })
 */
abstract class Facility extends EntityBase implements GroupOwnership, ModifiedAwareness, Sluggable, StateAwareness, Translatable
{
    use Feature\GroupOwnershipTrait;
    use Feature\ModifiedAwarenessTrait;
    use Feature\SluggableTrait;
    use Feature\StateAwarenessTrait;
    use Feature\TranslatableTrait;

    /**
     * @ORM\Column(type="custom_data_collection")
     */
    private $custom_data;

    public function __construct()
    {
        $this->translations = new ArrayCollection;
    }

    public function getCustomData() : array
    {
        return $this->custom_data;
    }

    public function setCustomData(array $entries) : void
    {
        $this->custom_data = $entries ?: null;
    }
}
