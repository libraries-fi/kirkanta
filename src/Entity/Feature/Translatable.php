<?php

namespace App\Entity\Feature;

use Doctrine\Common\Collections\Collection;

interface Translatable
{
    public function getTranslations() : Collection;
    public function hasTranslation(string $langcode) : bool;

    public function getDefaultLangcode() : ?string;
    public function setDefaultLangcode(string $langcode) : void;
}
