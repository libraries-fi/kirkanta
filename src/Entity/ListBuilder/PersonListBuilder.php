<?php

namespace App\Entity\ListBuilder;

use Doctrine\ORM\QueryBuilder;

class PersonListBuilder extends EntityListBuilder
{
    protected function createQueryBuilder() : QueryBuilder
    {
        $builder = parent::createQueryBuilder()
            ->addSelect('o')
            ->addSelect('od')
            ->join('e.library', 'o')
            ->join('o.translations', 'od', 'WITH', 'od.langcode = :langcode')
            ;

        $search = $this->getSearch();

        if (isset($search['name'])) {
            $builder->andWhere($builder->expr()->orx(
                $builder->expr()->like('LOWER(e.first_name)', ':name'),
                $builder->expr()->like('LOWER(e.last_name)', ':name'),
                $builder->expr()->like('LOWER(e.email)', ':name')
            ));
            $builder->setParameter('name', '%' . $search['name'] . '%');
        }

        if (isset($search['library'])) {
            $builder->andWhere('e.library = :library');
            $builder->setParameter('library', $search['library']);
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
                'name',
                'job_title' => ['mapping' => ['d.job_title']],
                'email',
                'phone',
                'library',
                'group'
            ])
            ->useAsTemplate('state')
            ->useAsTemplate('name')
            ->setSortable('name', true, ['last_name', 'first_name'])
            ->setSortable('job_title')
            ->setSortable('email')
            ->setSorting('name')
            ->transform('state', function($p) {
                if ($p->isPublished()) {
                    return '<i class="fa fa-square text-success" aria-label="{{ \'Published\'|trans }}"></i>';
                } else {
                    return '<i class="fa fa-square text-warning" aria-label="{{ \'Hidden\'|trans }}"></i>';
                }
            })
            ->transform('name', function() {
                return '<a href="{{ path("entity.person.edit", {person: row.id}) }}">{{ row.listName }}</a>';
            });

        return $table;
    }
}
