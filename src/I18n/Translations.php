<?php

namespace App\I18n;

use ArrayAccess;
use DomainException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class Translations
{
    const DEFAULT_LANGCODE = 'fi';

    private $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function __get($langcode)
    {
        return $this->data[$langcode] ?? null;
    }

    public function __set($langcode, $data)
    {
        $this->data[$langcode] = $data;
    }

    public function hasLanguage(string $langcode) : bool
    {
        return isset($this->data[$langcode]);
    }

    public function getLanguages() : array
    {
        return array_keys($this->data);
    }

    public function getValue(string $langcode, string $key) : ?string
    {
        if ($this->hasLanguage($langcode)) {
            return $this->data[$langcode][$key];
        } else {
            return null;
        }
    }

    public function setValue(string $langcode, string $key, $value) : void
    {
        if ($this->hasLanguage($langcode)) {
            $this->data[$langcode][$key] = $value;
        } else {
            throw new DomainException("This entity does not have language {$langcode} set");
        }
    }

    public function getTranslation(string $langcode) : array
    {
        if (!$this->hasLanguage($langcode)) {
            throw new DomainException(sprintf('Language \'%s\' is not contained', $langcode));
        }
        return $this->data[$langcode];
    }

    public function setTranslation(string $langcode, array $data) : void
    {
        $this->data[$langcode] = $data;
    }

    public function getData() : array
    {
        return $this->data;
    }
}
