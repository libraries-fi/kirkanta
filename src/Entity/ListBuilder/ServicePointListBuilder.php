<?php

namespace App\Entity\ListBuilder;

use App\Util\ServicePointTypes;

class ServicePointListBuilder extends LibraryListBuilder
{
    public function build(iterable $entities) : iterable
    {
        $types = new ServicePointTypes;
        $table = parent::build($entities)
            ->transform('name', function() {
                return '<a href="{{ path("entity.service_point.edit", {service_point: row.id}) }}">{{ row.name }}</a>';
            })
            ->transform('type', function($o) use ($types) {
                return $types->search($o->getType());
            })
        ;

        return $table;
    }
}
