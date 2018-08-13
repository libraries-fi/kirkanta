<?php

namespace App\Entity\ListBuilder;

use Doctrine\ORM\QueryBuilder;

class UserListBuilder extends EntityListBuilder
{
    protected function createQueryBuilder() : QueryBuilder
    {
        $builder = parent::createQueryBuilder()
            ->addOrderBy('e.username');

        $search = $this->getSearch();

        if (isset($search['name'])) {
            $builder->andWhere($builder->expr()->orx(
                $builder->expr()->like('e.username', ':name'),
                $builder->expr()->like('e.email', ':name')
            ));
            $builder->setParameter('name', '%' . $search['name'] . '%');
        }

        return $builder;
    }

    public function build(iterable $entities) : iterable
    {
        $table = parent::build($entities)
            ->setColumns(['name', 'email', 'group', 'roles', 'last_login'])
            ->useAsTemplate('name')
            ->transform('name', function() {
                return '<a href="{{ path("entity.user.edit", {user: row.id}) }}">{{ row.username }}</a>';
            })
            ->transform('last_login', function($user) {
                if ($time = $user->getLastLogin()) {
                    return $time->format('Y-m-d H:i');
                }
            })
            ->transform('roles', function($user) {
                return implode(', ', $user->getRoles());
            });

        return $table;
    }
}
