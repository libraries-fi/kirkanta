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
 * @ORM\Entity
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
     * Basename of the logo file
     *
     * @ORM\Column(type="string")
     */
    // private $logo;

    /**
     * @ORM\Column(type="string")
     */
    private $legacy_id;

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
     * @ORM\Column(type="json_document", options={"jsonb": true})
     */
    private $logo;

    public function __construct()
    {
        parent::__construct();
        $this->cities = new ArrayCollection;
        $this->libraries = new ArrayCollection;
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

    // public function getLogo() : ?string
    // {
    //     return $this->logo;
    // }
    //
    // public function setLogo(?string $logo) : void
    // {
    //     $this->logo = $logo;
    // }

    public function getLogo() : ?ConsortiumLogo
    {
        return $this->logo;
    }

    public function setLogo(?ConsortiumLogo $logo) : void
    {
        $this->logo = $logo;
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
}
