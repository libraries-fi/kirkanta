<?php

namespace App\Module\Finna\Entity;

use DateTime;
use App\Entity\Consortium;
use App\Entity\EntityBase;
use App\Entity\Feature;
use App\Entity\LibraryInterface;
use App\Entity\UserGroup;
use App\Entity\Feature\GroupOwnership;
use App\Entity\Feature\ModifiedAwareness;
use App\Entity\Feature\StateAwareness;
use App\Entity\Feature\Translatable;
use App\Module\ApiCache\Entity\Feature\ApiCacheable;
use App\Module\ApiCache\Entity\Feature\ApiCacheableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="finna_additions")
 */
class FinnaAdditions extends EntityBase implements ApiCacheable, GroupOwnership, ModifiedAwareness, Translatable
{
    use ApiCacheableTrait;
    use Feature\TranslatableTrait;
    use Feature\GroupOwnershipTrait;

    /**
     * Override ID definition to change GeneratedValue.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="NONE")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Consortium", mappedBy="finna_data", fetch="LAZY", cascade={"persist"})
     */
    private $consortium;

    /**
     * @ORM\OneToMany(targetEntity="FinnaOrganisationWebsiteLink", mappedBy="finna_organisation", orphanRemoval=true, cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     */
    private $links;

    /**
     * @ORM\OneToMany(targetEntity="FinnaAdditionsData", mappedBy="entity", orphanRemoval=true, cascade={"persist", "remove"}, fetch="EXTRA_LAZY", indexBy="langcode")
     */
    private $translations;

    /**
     * @ORM\OneToOne(targetEntity="App\Module\Finna\Entity\DefaultServicePointBinding", mappedBy="parent", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private $service_point;

    /**
     * @ORM\Column(type="string")
     *
     * NOTE: Represents ID on Finna.fi.
     *
     * Internal Kirkanta ID of this data entity is shared with Consortium.
     */
    private $finna_id;

    /**
     * @ORM\Column(type="integer")
     */
    private $finna_coverage;

    /**
     * @ORM\Column(type="boolean")
     */
    private $exclusive;

    public function __construct()
    {
        parent::__construct();
        $this->links = new ArrayCollection;
    }

    public function getConsortium() : ?Consortium
    {
        return $this->consortium;
    }

    public function setConsortium(Consortium $consortium) : void
    {
        $this->consortium = $consortium;

        if ($consortium->getOwner()) {
            $this->setOwner($consortium->getOwner());
        }

        if ($consortium->getFinnaData() != $this) {
            $consortium->setFinnaData($this);
        }

        if (!$this->id) {
          $this->id = $consortium->getId();
        }
    }

    public function getServicePoint() : ?LibraryInterface
    {
        if ($this->service_point) {
            return $this->service_point->getTargetEntity();
        } else {
            return null;
        }
    }

    public function setServicePoint(?LibraryInterface $entity) : void
    {
        if ($entity) {
            if ($this->service_point) {
                $this->service_point->setTargetEntity($entity);
            } else {
                $this->service_point = new DefaultServicePointBinding($this, $entity);
            }
        } else {
            $this->service_point = null;
        }
    }

    public function getLinks() : Collection
    {
        return $this->links;
    }

    public function getFinnaId() : ?string
    {
        return $this->finna_id;
    }

    public function setFinnaId(string $id) : void
    {
        $this->finna_id = $id;
    }

    public function getUsageInfo() : ?string
    {
        return $this->translations[$this->langcode]->getUsageInfo();
    }

    public function setUsageInfo(?string $text) : void
    {
        $this->translations[$this->langcode]->setUsageInfo($text);
    }

    public function getNotification() : ?string
    {
        return $this->translations[$this->langcode]->getNotification();
    }

    public function setNotification(?string $text) : void
    {
        $this->translations[$this->langcode]->setNotification($text);
    }

    public function getFinnaCoverage() : ?string
    {
        return $this->finna_coverage;
    }

    public function setFinnaCoverage(?string $percentage) : void
    {
        $this->finna_coverage = $percentage;
    }

    public function isExclusive() : bool
    {
        return $this->exclusive == true;
    }

    public function setExclusive(bool $state) : void
    {
        $this->exclusive = $state;
    }

    public function getState() : int
    {
        if ($this->consortium) {
            return $this->consortium->getState();
        } else {
            return StateAwareness::DRAFT;
        }
    }

    public function setState(int $state) : void
    {
        if ($this->consortium) {
            $this->consortium->setState($state);
        }
    }

    public function isPublished() : bool
    {
        return $this->getState() == StateAwareness::PUBLISHED;
    }

    public function setOwner(UserGroup $group) {
        if ($this->getOwner() != $group) {
            $this->group = $group;

            if ($consortium = $this->getConsortium()) {
                $consortium->setOwner($group);
            }
        }
    }

    public function getName() : string
    {
        return $this->getConsortium()->getName();
    }

    public function getCreated() : DateTime
    {
        return $this->getConsortium()->getCreated();
    }

    public function getModified() : DateTime
    {
        return $this->getConsortium()->getModified();
    }

    public function setModified(DateTime $time) : void
    {
        $this->getConsortium()->setModified($time);
    }
}
