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
 *     "foreign" = "ServicePoint",
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
     * @ORM\OneToMany(targetEntity="PhoneNumber", mappedBy="parent", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"weight": "ASC"})
     */
    protected $phone_numbers;

    /**
     * @ORM\OneToMany(targetEntity="EmailAddress", mappedBy="parent", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"weight": "ASC"})
     */
    protected $email_addresses;

    /**
     * @ORM\OneToMany(targetEntity="WebsiteLink", mappedBy="parent", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"weight": "ASC", "id": "ASC"})
     */
    protected $links;

    /**
     * @ORM\OneToMany(targetEntity="LibraryData", mappedBy="entity", orphanRemoval=true, cascade={"persist", "remove"}, fetch="EXTRA_LAZY", indexBy="langcode")
     */
    protected $translations;

    public function __construct()
    {
        parent::__construct();
        $this->links = new ArrayCollection;
        $this->email_addresses = new ArrayCollection;
        $this->phone_numbers = new ArrayCollection;
        
        $this->translations = new ArrayCollection;
    }
}
