<?php

namespace App\Module\Finna\Entity\ListBuilder;

use App\Entity\ListBuilder\EntityListBuilder;
use Doctrine\ORM\QueryBuilder;

class FinnaAdditionsListBuilder extends EntityListBuilder
{
    protected function createQueryBuilder() : QueryBuilder
    {
        $builder = parent::createQueryBuilder()
            ->addSelect('c')
            ->addSelect('cd')
            ->addSelect('sp')
            ->addSelect('ug')
            ->join('e.consortium', 'c')
            ->join('c.translations', 'cd', 'WITH', 'cd.langcode = e.default_langcode')
            ->join('e.service_point', 'sp')
            ->join('e.group', 'ug')
            ->andWhere('c.state >= 0')
            ;

        return $builder;
    }

    public function build(iterable $entities) : iterable
    {
        $table = parent::build($entities)
            ->setColumns([
                'state' => '',
                'name' => ['mapping' => ['cd.name']],
                'finna_id' => 'Finna ID',
                'group' => ['mapping' => ['c.group']]
            ])
            ->useAsTemplate('state')
            ->useAsTemplate('name')
            ->setSortable('name')
            ->transform('state', function ($o) {
                if ($o->isPublished()) {
                    return '<i class="fa fa-square text-success" title="{{ \'Published\'|trans }}"></i>';
                } else {
                    return '<i class="fa fa-square text-warning" title="{{ \'Draft\'|trans }}"></i>';
                }
            })
            ->transform('name', function () {
                return '<a href="{{ path("entity.finna_organisation.edit", {finna_organisation: row.id}) }}">{{ row.consortium.name }}</a>';
            });

        return $table;
    }
}
