<?php

namespace App\Entity\ListBuilder;

use Doctrine\ORM\QueryBuilder;
use App\Util\OrganisationBranchTypes;
use App\Util\OrganisationTypes;

class OrganisationListBuilder extends LibraryListBuilder
{
    public function build(iterable $entities) : iterable
    {
        $types = new OrganisationTypes;
        $branch_types = new OrganisationBranchTypes;

        $table = parent::build($entities)
            ->setColumns([
                'state',
                'name' => ['mapping' => ['d.name']],
                // 'type',
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
                return '<a href="{{ path("entity.organisation.edit", {organisation: row.id}) }}">{{ row.name }}</a>';
            })
            // ->transform('type', function($o) use($types) {
            //     return $types->search($o->getType());
            // })
            ;

        return $table;
    }
}
