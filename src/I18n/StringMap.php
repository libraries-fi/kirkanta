<?php

namespace App\I18n;

use ArrayAccess;
use ArrayIterator;
use Iterator;
use IteratorAggregate;

class StringMap implements ArrayAccess, IteratorAggregate
{
    use StringTranslationTrait;

    const SORT_KEYS = 0;
    const SORT_VALUES = 1;

    private $data;
    private $sorted;
    private $mode;

    public function __construct(array $data = [], int $sort_mode = self::SORT_KEYS)
    {
        $this->data = $data;
        $this->sorted = false;
        $this->mode = $sort_mode;
    }

    public function getData() : array
    {
        return $this->data;
    }

    public function search($value)
    {
        return array_search($value, $this->data);
    }

    protected function compareStrings(string $a, string $b) : int
    {
        return strcasecmp($this->t($a), $this->t($b));
    }

    public function offsetExists($key)
    {
        return array_key_exists($key, $this->data);
    }

    public function offsetGet($key)
    {
        return $this->data[$key] ?? null;
    }

    public function offsetSet($key, $value)
    {
        $this->sorted = false;

        if (is_null($key)) {
            $this->data[] = $value;
        } else {
            $this->data[$key] = $value;
        }
    }

    public function offsetUnset($key)
    {
        unset($this->data[$key]);
    }

    public function getIterator() : Iterator
    {
        if (!$this->sorted) {
            $this->sorted = true;
            if ($this->mode == self::SORT_KEYS) {
                uksort($this->data, [$this, 'compareStrings']);
            } elseif ($this->mode == self::SORT_VALUES) {
                uasort($this->data, [$this, 'compareStrings']);
            }
        }
        return new ArrayIterator($this->data);
    }
}
