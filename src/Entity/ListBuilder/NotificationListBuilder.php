<?php

namespace App\Entity\ListBuilder;

use Doctrine\ORM\QueryBuilder;

class NotificationListBuilder extends EntityListBuilder
{
    protected function createQueryBuilder() : QueryBuilder
    {
        $builder = parent::createQueryBuilder();
        $search = $this->getSearch();

        if (isset($search['subject'])) {
            $builder->andWhere('e.subject = :subject');
            $builder->setParameter('subject', '%' . $search['subject'] . '%');
        }

        return $builder;
    }

    public function build(iterable $entities) : iterable
    {
        $table = parent::build($entities)
            ->setColumns(['subject', 'created' => 'Posted'])
            ->setSorting('created', 'desc')
            ->useAsTemplate('subject')
            ->transform('subject', function () {
                return '<a href="{{ path("entity.notification.edit", {notification: row.id}) }}">{{ row.subject }}</a>';
            })
            ->transform('created', function ($date) {
                return $date->getCreated()->format('Y-m-d');
            });

        return $table;
    }
}
