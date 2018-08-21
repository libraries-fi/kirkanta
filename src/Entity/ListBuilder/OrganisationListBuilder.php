<?php

namespace App\Entity\ListBuilder;

use Doctrine\ORM\QueryBuilder;
use App\Util\OrganisationBranchTypes;

class OrganisationListBuilder extends LibraryListBuilder
{
    public function build(iterable $entities) : iterable
    {
        $branch_types = new OrganisationBranchTypes;

        $table = parent::build($entities)
            ->setColumns([
                'state',
                'name' => ['mapping' => ['d.name']],
                'group'
            ])
            ->setLabel('state', '')
            ->setSortable('name')
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
                return '<a href="{{ path("entity.organisation.edit", {organisation: row.id}) }}">{{ row.name }}</a>';
            })
            ;

        return $table;
    }
}
