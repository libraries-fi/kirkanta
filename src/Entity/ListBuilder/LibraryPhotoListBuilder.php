<?php

namespace App\Entity\ListBuilder;

use Doctrine\ORM\QueryBuilder;

class LibraryPhotoListBuilder extends EntityListBuilder
{
    protected function createQueryBuilder() : QueryBuilder
    {
        $builder = parent::createQueryBuilder()
            ->addOrderBy('e.weight')
            ;

        return $builder;
    }

    public function build(iterable $entities) : iterable
    {
        return parent::build($entities)
            ->setColumns([
                'filename' => ['label' => false],
                'name' => ['mapping' => ['d.name'], 'expand' => true],
            ])
            ->useAsTemplate('filename')
            ->useAsTemplate('name')
            ->transform('filename', function() {
                return "<img src=\"/files/photos/small/{{ row.filename }}\" alt=\"{{ row.name }}\" height=\"60\"/>";
            })
            ->transform('name', function() {
                return '
                    <a href="{{ path("entity.library.edit_resource", {library: row.library.id, "resource": "photos", "resource_id": row.id}) }}">{{ row.name }}</a>
                    <p>{{ row.description }}</p>
                ';
            })
            ;
    }
}
