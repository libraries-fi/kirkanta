<?php

namespace App\Module\ServiceTree\Entity\ListBuilder;

use Doctrine\ORM\QueryBuilder;
use App\Entity\ListBuilder\EntityListBuilder;

class ServiceCategoryListBuilder extends EntityListBuilder
{
    protected function createQueryBuilder() : QueryBuilder
    {
        $builder = parent::createQueryBuilder()
            ->leftJoin('e.items', 'items')
            ->addSelect('items')
            ->addOrderBy('e.parent')
            ->addOrderBy('e.name')
            ;

        return $builder;
    }

    public function build(iterable $entities) : iterable
    {
        $table = parent::build($entities)
            ->setColumns(['name', 'parent', 'items'])
            ->useAsTemplate('name')
            ->useAsTemplate('items')
            ->transform('name', function() {
                return '<a href="{{ path("entity.edit", {"type": "service_category", "id": row.id}) }}">{{ row.name }}</a>';
            })
            ->transform('parent', function($category) {
                if ($parent = $category->getParent()) {
                    return $parent->getName();
                }
            })
            ->transform('items', function($category) {
                return '{{ "%count% services"|trans({"%count%": row.items|length}) }}';
            });

        return $table;
    }
}
