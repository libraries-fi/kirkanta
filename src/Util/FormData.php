<?php

namespace App\Util;

use Doctrine\Common\Collections\Collection;

/**
 * Utility class to use with 'add entity' forms in place of arrays.
 *
 * This is necessary to allow property_path definitions inside forms, as the path syntax is different
 * between objets and arrays.
 */
class FormData
{
    private $data;

    public static function persistTemporaryTranslation(Collection $translations, string $langcode) : void
    {
        $tmplang = SystemLanguages::TEMPORARY_LANGCODE;

        if ($trdata = $translations->get($tmplang)) {
            $translations[$langcode] = $translations[$tmplang];
            $translations[$langcode]->setLangcode($langcode);
            unset($translations[$tmplang]);
        }
    }

    public function __construct(array $values = [])
    {
        $this->data = $values;
    }

    public function getValues() : array
    {
        return $this->data;
    }

    public function isNew() : bool
    {
        return true;
    }

    public function getDefaultLangcode() : string
    {
        return SystemLanguages::TEMPORARY_LANGCODE;
    }

    public function __call($method, $params) {
        $op = substr($method, 0, 3);
        $prop = strtolower(preg_replace('/([A-Z])/', '_$1', $method));
        $prop = substr($prop, 4);

        switch ($op) {
            case 'get':
                return $this->data[$prop];

            case 'set':
                $this->data[$prop] = reset($params);
                break;
        }
    }

    public function __get($key) {
        return $this->data[$key] ?? null;
    }

    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function __isset($key) {
        return isset($this->data);
    }
}
