<?php

namespace App\Entity\ListBuilder;

use App\Util\ServicePointTypes;

class ServicePointListBuilder extends LibraryListBuilder
{
    public function build(iterable $entities) : iterable
    {
        $types = new ServicePointTypes();
        $table = parent::build($entities)
            ->useAsTemplate('type')
            ->transform('name', function () {
                return '<a href="{{ path("entity.service_point.edit", {service_point: row.id}) }}">{{ row.name }}</a>';
            })
            ->transform('type', function ($o) use ($types) {
                $label = $types->search($o->getType());
                return "{% trans %}{$label}{% endtrans %}";
            })
        ;

        return $table;
    }
}
