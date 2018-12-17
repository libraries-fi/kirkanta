<?php

namespace App\Entity\Feature;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\I18n\Translations;
use App\Util\SystemLanguages;

trait TranslatableTrait
{
    /**
     * @ORM\Column(type="string")
     */
    protected $default_langcode;

    // protected $langcode = SystemLanguages::DEFAULT_LANGCODE;

    public function __get($property)
    {
        if ($property == 'langcode') {
            $this->langcode = $this->getDefaultLangcode();
            return $this->langcode;
        }
        exit('GET');
    }

    public function getTranslations() : Collection
    {
        /**
         * NOTE: $translations is a Doctrine propery so it needs to be declared in
         * the actual entity class.
         */
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

    public function getTranslation(string $langcode)
    {
        return $this->translations[$langcode];
    }

    public function hasTranslation(string $langcode) : bool
    {
        return $this->translations->containsKey($langcode);
    }

    public function getDefaultLanguage() : string
    {
        /**
         * FIXME: Remove this check and the static fallback after all entities have been migrated
         * to use $default_langcode.
         */

        var_dump('@deprecated');
        exit;

        if (isset($this->default_langcode)) {
            return $this->default_langcode;
        }
        return SystemLanguages::DEFAULT_LANGCODE;
    }

    public function setDefaultLangcode(string $langcode) : void
    {
        $this->default_langcode = $langcode;
    }

    public function getDefaultLangcode() : string
    {
        return $this->default_langcode;
    }

}
