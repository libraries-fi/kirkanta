<?php

namespace App\Util;

use Doctrine\Common\Collections\Collection;

class Collections
{
    public function addItems(Collection $collection, iterable $items) : Collection
    {
        foreach ($items as $item) {
            $collection->add($item);
        }
        return $collection;
    }
}
