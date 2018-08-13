<?php

namespace App\Entity\ListBuilder;

use Doctrine\ORM\QueryBuilder;

class RegionalLibraryListBuilder extends EntityListBuilder
{
    protected function createQueryBuilder() : QueryBuilder
    {
        $builder = parent::createQueryBuilder();
        $search = $this->getSearch();

        if (isset($search['name'])) {
            $builder->andWhere('d.name = :name');
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
            ->transform('name', function() {
                return '<a href="{{ path("entity.regional_library.edit", {regional_library: row.id}) }}">{{ row.name }}</a>';
            });

        return $table;
    }
}
