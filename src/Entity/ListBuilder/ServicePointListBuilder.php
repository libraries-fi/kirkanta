<?php

namespace App\Entity\ListBuilder;

use Doctrine\ORM\QueryBuilder;
use App\Util\OrganisationBranchTypes;

class ServicePointListBuilder extends LibraryListBuilder
{
    public function build(iterable $entities) : iterable
    {
        $table = parent::build($entities)
            ->transform('name', function() {
                return '<a href="{{ path("entity.service_point.edit", {service_point: row.id}) }}">{{ row.name }}</a>';
            })
        ;

        return $table;
    }
}
