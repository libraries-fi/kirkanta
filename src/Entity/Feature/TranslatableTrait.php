<?php

namespace App\Entity\Feature;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\I18n\Translations;
use App\Util\SystemLanguages;

trait TranslatableTrait
{
    protected $langcode = Translations::DEFAULT_LANGCODE;

    public function getTranslations() : Collection
    {
        // $translations is a Doctrine propery so it needs to be declared in the actual entity class.
        return $this->translations;
    }

    public function setTranslations(iterable $data) : void
    {
        if (!($data instanceof Collection)) {
            $data = new ArrayCollection($data);
        }
        foreach ($data as $data_entity) {
            $data_entity->setEntity($this);
        }
        $this->translations = $data;
    }

    public function hasTranslation(string $langcode) : bool
    {
        return $this->translations->containsKey($langcode);
    }

    public function getDefaultLanguage() : string
    {
        return SystemLanguages::DEFAULT_LANGCODE;
    }
}
