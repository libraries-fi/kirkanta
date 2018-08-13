<?php

namespace App\Component\Iterator;

use ArrayIterator;
use Iterator;
use IteratorAggregate;

class IdentityIterator implements Iterator
{
    public function __construct(iterable $data)
    {
        if ($data instanceof Iterator) {
            $this->iterator = $data;
        } elseif ($data instanceof IteratorAggregate) {
            $this->iterator = $data->getIterator();
        } else if (is_array($data)) {
            $this->iterator = new ArrayIterator($data);
        }
    }

    public function current()
    {
        return $this->iterator->current();
    }

    public function key()
    {
        return $this->iterator->key();
    }

    public function next() : void
    {
        $this->iterator->next();
    }

    public function rewind() : void
    {
        $this->iterator->rewind();
    }

    public function valid() : bool
    {
        return $this->iterator->valid();
    }
}
