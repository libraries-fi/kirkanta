<?php

namespace App\Component\Iterator;

class TransformIterator extends IdentityIterator
{
    private $transformers;

    public function __construct(iterable $data, callable $transformer = null)
    {
        parent::__construct($data);
        $this->transformers = $transformer ? [$transformer] : [];
    }

    public function current()
    {
        $value = parent::current();

        foreach ($this->transformers as $transformer) {
            $value = $transformer($value, $this->key());
        }

        return $value;
    }
}
