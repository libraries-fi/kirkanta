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
            ->andWhere('COALESCE(IDENTITY(e.parent), 0) = :parent')
            ->setParameter('parent', 0)
            ;

        $search = $this->getSearch();

        if (isset($search['name'])) {
            $builder->andWhere('LOWER(d.name) LIKE LOWER(:name)');
            $builder->setParameter('name', '%' . $search['name'] . '%');
        }

        // CheckboxType always returns a value.
        if (empty($search['past_periods'])) {
            $builder->andWhere('(e.valid_from >= :now OR e.valid_until IS NULL) OR e.valid_until >= :now');
            $builder->setParameter('now', (new DateTime)->format('Y-m-d'));
        }

        if (isset($search['department'])) {
            // var_dump($search['department']);
            $builder->andWhere('e.department = :department');
            $builder->setParameter('department', $search['department']);
        }

        return $builder;
    }

    public function build(iterable $entities) : iterable
    {
        $table = parent::build($entities)
            ->setColumns([
                'state' => ['label' => false],
                'name' => ['mapping' => ['d.name']],
                'type' => ['label' => false],
                'valid_from',
                'valid_until',
                'owner'
            ])
            ->setSortable('name')
            ->setSortable('valid_from')
            ->setSortable('valid_until')
            ->setSorting('valid_from', 'desc')
            ->useAsTemplate('state')
            ->useAsTemplate('name')
            ->useAsTemplate('type')
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
            ->transform('valid_from', function($p) {
                return $p->getValidFrom()->format('Y-m-d');
            })
            ->transform('valid_until', function($p) {
                if ($p->getValidUntil()) {
                    return $p->getValidUntil()->format('Y-m-d');
                }
            })
            ->transform('type', function($p) {
                if ($p->isLegacyFormat()) {
                    // FIXME: Remove this condition after dropping the section field!
                    return '<span class="badge badge-pill badge-danger">{% trans %}Legacy period{% endtrans %}</span>';
                }
                if (!$p->isContinuous()) {
                    return '<span class="badge badge-pill badge-warning">{% trans %}Exception schedules{% endtrans %}</span>';
                }
            })
            ;

        return $table;
    }
}
