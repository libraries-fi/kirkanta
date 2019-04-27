<?php

namespace App\Entity\ListBuilder;

use Doctrine\ORM\QueryBuilder;

class UserGroupListBuilder extends EntityListBuilder
{
    protected function createQueryBuilder() : QueryBuilder
    {
        $builder = parent::createQueryBuilder()
            ->addOrderBy('e.name');

        $search = $this->getSearch();

        if (isset($search['name'])) {
            $builder->andWhere('e.name LIKE :name');
            $builder->setParameter('name', '%' . $search['name'] . '%');
        }

        return $builder;
    }

    public function build(iterable $entities) : iterable
    {
        $table = parent::build($entities)
            ->setColumns(['name', 'parent', 'description'])
            ->useAsTemplate('name')
            ->transform('name', function () {
                return '<a href="{{ path("entity.user_group.edit", {user_group: row.id}) }}">{{ row.name }}</a>';
            });

        return $table;
    }
}
