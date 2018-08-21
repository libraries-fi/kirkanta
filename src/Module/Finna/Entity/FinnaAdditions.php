<?php

namespace App\Module\Finna\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Consortium;
use App\Entity\EntityBase;
use App\Entity\Feature;
use App\Entity\Library;
use App\Entity\UserGroup;
use App\Entity\Feature\GroupOwnership;
use App\Entity\Feature\StateAwareness;
use App\Entity\Feature\Translatable;
use App\Module\ApiCache\Entity\Feature\ApiCacheable;
use App\Module\ApiCache\Entity\Feature\ApiCacheableTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="finna_additions")
 */
class FinnaAdditions extends EntityBase implements ApiCacheable, GroupOwnership, Translatable
{
    use ApiCacheableTrait;
    use Feature\TranslatableTrait;
    use Feature\GroupOwnershipTrait;

    /**
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
     * @ORM\OneToMany(targetEntity="FinnaAdditionsData", mappedBy="entity", orphanRemoval=true, cascade={"persist", "remove"}, fetch="EXTRA_LAZY", indexBy="langcode")
     */
    private $translations;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Library")
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

    public function getConsortium() : Consortium
    {
        return $this->consortium;
    }

    public function setConsortium(Consortium $consortium) : void
    {
        $this->consortium = $consortium;
        $this->setOwner($consortium->getOwner());

        if ($consortium->getFinnaData() != $this) {
            $consortium->setFinnaData($this);
        }

        if (!$this->id) {
          $this->id = $consortium->getId();
        }
    }

    public function getFinnaId() : string
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

    public function setServicePoint(?Library $organisation) : void
    {
        $this->service_point = $organisation;
    }

    public function getServicePoint() : ?Library
    {
        return $this->service_point;
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

    public function hasOwner() : bool
    {
        if ($this->consortium) {
            return $this->consortium->hasOwner();
        }
    }

    public function getOwner() : UserGroup
    {
        if ($this->consortium) {
            return $this->consortium->hasOwner();
        }
    }

    public function getGroup() : UserGroup
    {
        if ($this->consortium) {
            return $this->consortium->getGroup();
        }
    }

    public function getName() : string
    {
        return $this->getConsortium()->getName();
    }
}
