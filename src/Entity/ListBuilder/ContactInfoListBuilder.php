<?php

namespace App\Entity\ListBuilder;

use DateTime;
use Doctrine\ORM\QueryBuilder;

class ContactInfoListBuilder extends EntityListBuilder
{
    public function build(iterable $entities) : iterable
    {
        $table = parent::build($entities)
            ->setColumns([
                'name' => ['mapping' => ['d.name']],
                'contact' => ['label' => 'Phone Number', 'expand' => true],
            ]);

        return $table;
    }
}
