<?php

namespace App\Entity;

use App\Entity\Feature\GroupOwnership;
use App\Entity\Feature\ModifiedAwareness;
use App\Entity\Feature\Sluggable;
use App\Entity\Feature\StateAwareness;
use App\Entity\Feature\Translatable;
use App\I18n\Translations;
use App\Module\ApiCache\Entity\Feature\ApiCacheable;
use App\Module\ApiCache\Entity\Feature\ApiCacheableTrait;
use App\Module\Finna\Entity\FinnaAdditions;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Doctrine\ConsortiumRepository")
 * @ORM\Table(name="consortiums")
 */
class Consortium extends EntityBase implements ApiCacheable, GroupOwnership, ModifiedAwareness, Sluggable, StateAwareness, Translatable
{
    use ApiCacheableTrait;
    use Feature\GroupOwnershipTrait;
    use Feature\ModifiedAwarenessTrait;
    use Feature\SluggableTrait;
    use Feature\StateAwarenessTrait;
    use Feature\TranslatableTrait;

    /**
     * @ORM\OneToMany(targetEntity="ConsortiumData", mappedBy="entity", orphanRemoval=true, cascade={"persist", "remove"}, fetch="EXTRA_LAZY", indexBy="langcode")
     */
    private $translations;

    /**
     * @ORM\OneToMany(targetEntity="City", mappedBy="consortium")
     */
    private $cities;

    /**
     * @ORM\OneToMany(targetEntity="Library", mappedBy="consortium")
     */
    private $libraries;

    /**
     * @ORM\OneToOne(targetEntity="App\Module\Finna\Entity\FinnaAdditions", inversedBy="consortium", cascade={"remove"}, orphanRemoval=true)
     */
    private $finna_data;

    /**
     * @ORM\OneToOne(targetEntity="ConsortiumLogo", inversedBy="consortium", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $logo;

    /**
     * Identifier of this entity in the Elasticsearch database of API v1/v2 Elasticsearch DB.
     *
     * @deprecated
     *
     * @ORM\Column(type="string")
     */
    private $legacy_id;

    /**
     * Filename of attached logo. Used to store filename on disk while migrating to entities.
     *
     * @see App\Module\MigrationsV3\Command\CreateConsortiumLogoEntities
     * @deprecated
     *
     * @ORM\Column(type="string")
     */
    private $old_logo_filename;

    public function __construct()
    {
        parent::__construct();
        $this->cities = new ArrayCollection();
        $this->libraries = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getName() : string
    {
        return $this->translations[$this->langcode]->getName();
    }

    public function setName(string $name) : void
    {
        $this->translations[$this->langcode]->setName($name);
    }

    public function getHomepage() : ?string
    {
        return $this->translations[$this->langcode]->getHomepage();
    }

    public function setHomepage(string $url) : void
    {
        $this->translations[$this->langcode]->setHomePage($url);
    }

    public function getDescription() : ?string
    {
        return $this->translations[$this->langcode]->getDescription();
    }

    public function setDescription(?string $description) : void
    {
        $this->translations[$this->langcode]->setDescription($description);
    }

    public function getLogo() : ?ConsortiumLogo
    {
        return $this->logo;
    }

    public function setLogo(?ConsortiumLogo $logo) : void
    {
        $this->logo = $logo;

        if ($this->logo) {
            $this->logo->setConsortium($this);
        }
    }

    public function getFinnaData() : ?FinnaAdditions
    {
        return $this->finna_data;
    }

    public function setFinnaData(?FinnaAdditions $data) : void
    {
        $this->finna_data = $data;
    }

    public function getLibraries() : Collection
    {
        return $this->libraries;
    }

    public function getOldLogoFilename() : ?string
    {
        return $this->old_logo_filename;
    }

    public function isFinnaExclusive() : bool
    {
        if ($finna_data = $this->getFinnaData()) {
            return $finna_data->isExclusive();
        } else {
            return false;
        }
    }

    public function setOwner(UserGroup $group) : void
    {
        if ($this->getOwner() != $group) {
            $this->group = $group;

            if ($finna = $this->getFinnaData()) {
                $finna->setOwner($group);
            }
        }
    }
}
