<?php

namespace App\Entity\ListBuilder;

use DateTime;
use Doctrine\ORM\QueryBuilder;

class PeriodListBuilder extends EntityListBuilder
{
    protected function createQueryBuilder() : QueryBuilder
    {
        $builder = parent::createQueryBuilder()
            ->addOrderBy('d.name')
            ->andWhere('COALESCE(IDENTITY(e.library), 0) = :library')
            ->setParameter('library', 0)
            ;

        $search = $this->getSearch();

        if (isset($search['name'])) {
            $builder->andWhere('d.name = :name');
            $builder->setParameter('name', '%' . $search['name'] . '%');
        }

        if (isset($search['only_valid'])) {
            $builder->andWhere('e.valid_from >= :now OR e.valid_until >= :now');
            $builder->setParameter('now', new DateTime);
        }

        return $builder;
    }

    public function build(iterable $entities) : iterable
    {
        $table = parent::build($entities)
            ->setColumns([
                'state' => ['label' => false],
                'name' => ['mapping' => ['d.name'], 'expand' => true],
                'valid_from',
                'valid_until',
                'department' => ['expand' => true],
                'owner'
            ])
            ->setSortable('name')
            ->setSortable('valid_from')
            ->setSortable('valid_until')
            ->setSorting('valid_from', 'desc')
            ->useAsTemplate('state')
            ->useAsTemplate('name')
            ->transform('state', function($p) {
                if ($p->isActive()) {
                    $class = 'text-success';
                    $label = 'Active';
                } elseif ($p->isExpired()) {
                    $class = 'text-muted';
                    $label = 'Expired';
                } else {
                    $class = 'text-info';
                    $label = 'Upcoming';
                }
                return "<i class=\"fa fa-square {$class}\" title=\"{$label}\"></i>";
            })
            ->transform('name', function() {
                return '<a href="{{ path("entity.period.edit", {period: row.id}) }}">{{ row.name }}</a>';
            })
            ->transform('department', function($p) {
                if ($department = $p->getDepartment()) {
                    return $department->getName();
                }
            })
            ->transform('valid_from', function($p) {
                return $p->getValidFrom()->format('Y-m-d');
            })
            ->transform('valid_until', function($p) {
                if ($p->getValidUntil()) {
                    return $p->getValidUntil()->format('Y-m-d');
                }
            });

        return $table;
    }
}
