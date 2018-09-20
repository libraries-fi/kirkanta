<?php

namespace App\Entity\ListBuilder;

use Doctrine\ORM\QueryBuilder;
use App\Util\ServiceTypes;

class ServiceListBuilder extends EntityListBuilder
{
    protected function createQueryBuilder() : QueryBuilder
    {
        $builder = parent::createQueryBuilder();
        $search = $this->getSearch();

        if (isset($search['name'])) {
            $builder->andWhere('d.name LIKE :name');
            $builder->setParameter('name', '%' . $search['name'] . '%');
        }

        if (isset($search['type'])) {
            $builder->andWhere('e.type = :type');
            $builder->setParameter('type', $search['type']);
        }

        return $builder;
    }

    public function build(iterable $entities) : iterable
    {
        $types = new ServiceTypes;
        $table = parent::build($entities)
            ->setColumns([
                'name' => ['mapping' => ['d.name']],
                'type',
                'description',
                'instances'
            ])
            ->setSortable('name')
            ->useAsTemplate('name')
            ->useAsTemplate('instances')
            ->transform('name', function() {
                return '<a href="{{ path("entity.service.edit", {service: row.id}) }}">{{ row.name }}</a>';
            })
            ->transform('type', function($s) use ($types) {
                return $types->search($s->getType());
            })
            ->transform('instances', function($s) {
                $count = count($s->getInstances());

                return "
                    {% if row.instances|length == 0 %}
                        {% trans %}Unused{% endtrans %}
                    {% else %}
                        <a href=\"{{ path('entity.service.usage', {service: row.id}) }}\">
                            {% transchoice {$count} with {'%count': {$count}} %}
                                {1} 1 instance|[2,Inf[ %count% instances
                            {% endtranschoice %}
                        </a>
                    {% endif %}
                ";

                return $count == 1
                    ? '{% trans %}1 instance{% endtrans %}'
                    : "{% trans with {'%count%': {$count}} %}%count% instances{% endtrans %}"
                    ;
            });

        return $table;
    }
}
