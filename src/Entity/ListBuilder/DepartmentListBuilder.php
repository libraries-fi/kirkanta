<?php

namespace App\Entity\ListBuilder;

use Doctrine\ORM\QueryBuilder;
use App\Util\OrganisationBranchTypes;
use App\Util\OrganisationTypes;

class DepartmentListBuilder extends EntityListBuilder
{
    protected function createQueryBuilder() : QueryBuilder
    {
        $builder = parent::createQueryBuilder();
        $search = $this->getSearch();

        if (isset($search['name'])) {
            $builder->andWhere($builder->expr()->orx(
                $builder->expr()->like('LOWER(d.name)', ':name'),
                $builder->expr()->like('LOWER(d.short_name)', ':name')
            ));
            $builder->setParameter('name', '%' . $search['name'] . '%');
        }

        if (isset($search['type'])) {
            $builder->andWhere('e.type = :type');
            $builder->setParameter('type', $search['type']);
        }

        if (isset($search['state'])) {
            $builder->andWhere('e.state = :state');
            $builder->setParameter('state', $search['state']);
        }

        return $builder;
    }

    public function build(iterable $entities) : iterable
    {
        $table = parent::build($entities)
            ->setColumns([
                'state',
                'name' => ['mapping' => ['d.name']],
            ])
            ->setLabel('state', '')
            ->setSortable('name')
            ->useAsTemplate('state')
            ->useAsTemplate('name')
            ->transform('state', function($o) {
                if ($o->isPublished()) {
                    return '<i class="fa fa-square text-success" title="{{ \'Published\'|trans }}"></i>';
                } else {
                    return '<i class="fa fa-square text-warning" title="{{ \'Draft\'|trans }}"></i>';
                }
            })
            ->transform('name', function() {
                return '<a href="{{ path("entity.organisation.edit", {organisation: row.id}) }}">{{ row.name }}</a>';
            });

        return $table;
    }
}
