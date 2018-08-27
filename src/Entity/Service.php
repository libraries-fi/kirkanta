<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Feature\GroupOwnership;
use App\Entity\Feature\Translatable;
use App\I18n\Translations;
use App\Module\ApiCache\Entity\Feature\ApiCacheable;
use App\Module\ApiCache\Entity\Feature\ApiCacheableTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="services")
 */
class Service extends EntityBase implements ApiCacheable, Translatable
{
    use ApiCacheableTrait;
    use Feature\SluggableTrait;
    use Feature\TranslatableTrait;

    /**
     * @ORM\Column(type="string")
     */
    private $type;

    /**
     * @ORM\Column(type="integer")
     */
    private $helmet_type_priority;

    /**
     * @ORM\OneToMany(targetEntity="ServiceData", mappedBy="entity", orphanRemoval=true, cascade={"persist", "remove"}, fetch="EXTRA_LAZY", indexBy="langcode")
     */
    private $translations;

    /**
     * @ORM\OneToMany(targetEntity="ServiceInstance", mappedBy="template")
     */
    private $instances;

    public function __construct()
    {
        parent::__construct();
        $this->instances = new ArrayCollection;
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

    public function getDescription() : ?string
    {
        return $this->translations[$this->langcode]->getDescription();
    }

    public function setDescription(?string $description) : void
    {
        $this->translations[$this->langcode]->setDescription($description);
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function setType($type) : void
    {
        $this->type = $type;
    }

    public function getHelmetTypePriority()
    {
        return $this->helmet_type_priority;
    }

    public function setHelmetTypePriority($value) : void
    {
        $this->helmet_type_priority = $value;
    }

    public function getInstances() : Collection
    {
        return $this->instances;
    }
}
