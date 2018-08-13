<?php

namespace App\I18n;

trait StringTranslationTrait
{
    private $translator;

    public function getTranslator()
    {
        return $this->translator;
    }

    public function setTranslator($translator)
    {
        return $this->translator = $translator;
    }

    public function t(string $string) : string
    {
        if ($translator = $this->getTranslator()) {
            return $translator->trans($string);
        } else {
            return $string;
        }
    }
}
