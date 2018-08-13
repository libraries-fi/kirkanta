<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Feature\Sluggable;
use App\Entity\Feature\Translatable;
use App\I18n\Translations;

/**
 * @ORM\Entity
 * @ORM\Table(name="provincial_libraries")
 */
class RegionalLibrary extends EntityBase implements Sluggable, Translatable
{
    use Feature\SluggableTrait;
    use Feature\TranslatableTrait;

    /**
     * @ORM\OneToMany(targetEntity="RegionalLibraryData", mappedBy="entity", orphanRemoval=true, cascade={"persist", "remove"}, fetch="EXTRA_LAZY", indexBy="langcode")
     */
    private $translations;

    /**
     * @ORM\Column(type="string")
     */
    private $legacy_id;

    public function __toString()
    {
        return $this->getName();
    }

    public function getName() : string
    {
        return $this->translations[$this->langcode]->getName();
    }

    public function setName($name) : void
    {
        $this->translations[$this->langcode]->setName($name);
    }

    public function getProvince() : string
    {
        return $this->translations[$this->langcode]->getProvince();
    }

    public function setProvince(string $name) : void
    {
        $this->translations[$this->langcode]->setProvince($name);
    }
}
