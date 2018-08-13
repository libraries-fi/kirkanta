<?php

namespace App\Entity\ListBuilder;

use App\Entity\Consortium;
use Doctrine\ORM\QueryBuilder;

class ConsortiumListBuilder extends EntityListBuilder
{
    protected function createQueryBuilder() : QueryBuilder
    {
        $builder = parent::createQueryBuilder()
            ->addSelect('fd')
            ->leftJoin('e.finna_data', 'fd')
            ->andWhere('e.finna_data IS NULL OR fd.exclusive = FALSE')
            ->orderBy('d.name');

        $search = $this->getSearch();

        if (isset($search['name'])) {
            $builder->andWhere('d.name = :name');
            $builder->setParameter('name', '%' . $search['name'] . '%');
        }

        if (isset($search['group'])) {
            $builder->andWhere('e.group = :group');
            $builder->setParameter('group', $search['group']);
        }

        return $builder;
    }

    public function build(iterable $entities) : iterable
    {
        $table = parent::build($entities)
            ->setColumns([
                'state' => '',
                'name' => ['mapping' => ['d.name']],
                'finna_data' => '',
                'homepage',
                'group'
            ])
            ->useAsTemplate('state')
            ->useAsTemplate('name')
            ->useAsTemplate('finna_data')
            ->setSortable('name')
            ->transform('state', function($o) {
                if ($o->isPublished()) {
                    return '<i class="fa fa-square text-success" title="{{ \'Published\'|trans }}"></i>';
                } else {
                    return '<i class="fa fa-square text-warning" title="{{ \'Draft\'|trans }}"></i>';
                }
            })
            ->transform('name', function() {
                return '<a href="{{ path("entity.consortium.edit", {consortium: row.id}) }}">{{ row.name }}</a>';
            })
            ->transform('finna_data', function(Consortium $consortium) {
                if ($consortium->getFinnaData()) {

                    return '<i class="fas fa-link" aria-label="{{ \'Shared to Finna\'|trans }}" title="{{ \'Shared to Finna\'|trans }}"></i>';
                    return '<img src="/images/finna.logo.no-text.svg" class="icon" alt="{{ \'Shared to Finna\'|trans }}" title="{{ \'Shared to Finna\'|trans }}" class="finna-enabled"/>';
                }
                // return '{% if row.finnaData %}<i class="fas fa-link" aria-label="{{ \'Shared to Finna\'|trans }}" title="{{ \'Shared to Finna\'|trans }}"></i>{% endif %}';
            });

        return $table;
    }
}
