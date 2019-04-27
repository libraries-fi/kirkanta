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
                'filename' => [
                    'label' => false,
                    'size' => 120
                ],
                'name' => [
                    'mapping' => ['d.name']
                ],
                'dimensions' => [
                    'size' => 160
                ],
            ])
            ->useAsTemplate('filename')
            ->useAsTemplate('name')
            ->useAsTemplate('dimensions')
            ->transform('filename', function () {
                return "<img src=\"/files/photos/small/{{ row.filename }}\" alt=\"{{ row.name }}\" width=\"120\"/>";
            })
            ->transform('name', function () {
                return '
                    <a href="{{ path("entity.library.pictures.edit", {library: row.library.id, "resource": "photos", "resource_id": row.id}) }}">{{ row.name }}</a>
                    <p>{{ row.description }}</p>
                ';
            })
            ->transform('dimensions', function ($p) {
                if (!$p->getDimensions()) {
                    return '<i>{% trans %}Invalid data{% endtrans %}</i>';
                }

                $data[] = '{{ row.dimensions|join("x") }} px';

                if ($p->getPixelCount() < 1280 * 960) {
                    $data[] = '<small class="d-block text-danger">{% trans %}Picture is too small{% endtrans %}</small>';
                }

                return implode(PHP_EOL, $data);
            })
            ;
    }
}
