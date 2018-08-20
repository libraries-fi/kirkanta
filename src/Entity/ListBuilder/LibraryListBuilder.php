<?php

namespace App\Entity\ListBuilder;

use Doctrine\ORM\QueryBuilder;
use App\Util\OrganisationBranchTypes;
use App\Util\OrganisationTypes;

class LibraryListBuilder extends EntityListBuilder
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

        if (isset($search['branch_type'])) {
            $builder->andWhere('e.branch_type = :branch_type');
            $builder->setParameter('branch_type', $search['branch_type']);
        }

        if (isset($search['group'])) {
            $builder->andWhere('e.group = :group');
            $builder->setParameter('group', $search['group']);
        }

        if (isset($search['state'])) {
            $builder->andWhere('e.state = :state');
            $builder->setParameter('state', $search['state']);
        }

        return $builder;
    }

    public function build(iterable $entities) : iterable
    {
        $types = new OrganisationTypes;
        $branch_types = new OrganisationBranchTypes;

        $table = parent::build($entities)
            ->setColumns([
                'state',
                'name' => ['mapping' => ['d.name']],
                // 'type',
                'branch_type',
                'group'
            ])
            ->setLabel('state', '')
            ->setSortable('name')
            // ->setSortable('type')
            ->setSortable('group')
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
                return '<a href="{{ path("entity.library.edit", {library: row.id}) }}">{{ row.name }}</a>';
            })
            // ->transform('type', function($o) use($types) {
            //     return $types->search($o->getType());
            // })
            ->transform('branch_type', function($o) use($branch_types) {
                return $branch_types->search($o->getBranchType());
            });

        return $table;
    }
}
