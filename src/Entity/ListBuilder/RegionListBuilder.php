<?php

namespace App\Entity\ListBuilder;

use Doctrine\ORM\QueryBuilder;

class RegionListBuilder extends EntityListBuilder
{
    protected function createQueryBuilder() : QueryBuilder
    {
        $builder = parent::createQueryBuilder();
        $search = $this->getSearch();

        if (isset($search['name'])) {
            $builder->andWhere('d.name LIKE :name');
            $builder->setParameter('name', '%' . $search['name'] . '%');
        }

        return $builder;
    }

    public function build(iterable $entities) : iterable
    {
        $table = parent::build($entities)
            ->setColumns(['name' => ['mapping' => ['d.name']]])
            ->setSortable('name')
            ->useAsTemplate('name')
            ->transform('name', function () {
                return '<a href="{{ path("entity.region.edit", {region: row.id}) }}">{{ row.name }}</a>';
            });

        return $table;
    }
}
