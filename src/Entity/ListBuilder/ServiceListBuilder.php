<?php

namespace App\Entity\ListBuilder;

use Doctrine\ORM\QueryBuilder;
use App\Util\ServiceTypes;

class ServiceListBuilder extends EntityListBuilder
{
    protected function createQueryBuilder() : QueryBuilder
    {
        $builder = parent::createQueryBuilder();
        $search = $this->getSearch();

        if (isset($search['name'])) {
            $builder->andWhere('d.name LIKE :name');
            $builder->setParameter('name', '%' . $search['name'] . '%');
        }

        if (isset($search['type'])) {
            $builder->andWhere('e.type = :type');
            $builder->setParameter('type', $search['type']);
        }

        return $builder;
    }

    public function build(iterable $entities) : iterable
    {
        $types = new ServiceTypes;
        $table = parent::build($entities)
            ->setColumns(['name' => ['mapping' => ['d.name']], 'type', 'description'])
            ->setSortable('name')
            ->useAsTemplate('name')
            ->transform('name', function() {
                return '<a href="{{ path("entity.service.edit", {service: row.id}) }}">{{ row.name }}</a>';
            })
            ->transform('type', function($s) use ($types) {
                return $types->search($s->getType());
            });

        return $table;
    }
}
