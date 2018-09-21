<?php

namespace App\Util;

/**
 * Utility class to use with 'add entity' forms in place of arrays.
 *
 * This is necessary to allow property_path definitions inside forms, as the path syntax is different
 * between objets and arrays.
 */
class FormData
{
    private $data;

    public function __construct(array $values = [])
    {
        $this->data = $values;
    }

    public function getValues() : array
    {
        return $this->data;
    }

    public function __call($method, $params) {
        if (substr($method, 0, 3) == 'get') {
            $prop = strtolower(preg_replace('/([A-Z])/', '_$1', $method));
            $prop = substr($prop, 4);
            return $this->data[$prop];
        }
    }

    public function __get($key) {
        return $this->data[$key] ?? null;
    }

    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }
}
