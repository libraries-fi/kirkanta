<?php

namespace App\Entity\ListBuilder;

use Doctrine\ORM\QueryBuilder;

class CityListBuilder extends EntityListBuilder
{
    protected function createQueryBuilder() : QueryBuilder
    {
        $builder = parent::createQueryBuilder()
            ->addSelect('r')
            ->addSelect('rd')
            ->addSelect('rl')
            ->addSelect('rld')
            ->addSelect('c')
            ->addSelect('cd')
            ->join('e.region', 'r')
            ->join('r.translations', 'rd', 'WITH', 'rd.langcode = :langcode')
            ->join('e.regional_library', 'rl')
            ->join('rl.translations', 'rld', 'WITH', 'rld.langcode = :langcode')
            ->join('e.consortium', 'c')
            ->join('c.translations', 'cd', 'WITH', 'cd.langcode = :langcode')
            ;

        /*
         * NOTE: This join is unused but necessary for optimization,
         * because Doctrine would otherwise fetch them in separate queries.
         * This is because Doctrine always fetches OneToOne relationship
         * when the queried entity is on the inverse side.
         */
        // $builder
        //     ->addSelect('cfd')
        //     ->join('c.finna_data', 'cfd')
        //     ;

        $search = $this->getSearch();

        if (isset($search['name'])) {
            $builder->andWhere('LOWER(d.name) LIKE LOWER(:name)');
            $builder->setParameter('name', '%' . $search['name'] . '%');
        }

        return $builder;
    }

    public function build(iterable $entities) : iterable
    {
        $table = parent::build($entities)
            ->setColumns([
                'name' => ['mapping' => ['d.name']],
                'region',
                'consortium',
                'regional_library'
            ])
            ->setSortable('name')
            ->useAsTemplate('name')
            ->transform('name', function() {
                return '<a href="{{ path("entity.city.edit", {city: row.id}) }}">{{ row.name }}</a>';
            });

        return $table;
    }
}
