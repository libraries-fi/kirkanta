<?php

namespace KirjastotFi\KirkantaApiBundle\Extension\Query;

use Doctrine\ORM\QueryBuilder;
use KirjastotFi\KirkantaApiBundle\CompilerContext;
use App\Entity\Person;

class PersonExtension implements QueryExtensionInterface
{
    public function supports(CompilerContext $context) : bool
    {
        return $context->getEntityClass() == Person::class;
    }

    public function build(QueryBuilder $builder, CompilerContext $context) : void
    {
        QueryHelper::filterCreated($builder, $context);
        QueryHelper::filterModified($builder, $context);

        $builder->addSelect('data');

        if ($name = $context->params->get('name')) {
            $langcode = $context->langcode ?? 'fi';
            $builder->andWhere('LOWER(CONCAT(e.first_name, \' \', e.last_name)) LIKE :name');
            $builder->setParameter('name', "%{$name}%");

            $context->params->remove('name');
        }

        if ($with = QueryHelper::withForParam('organisation', $context)) {
            $builder->innerJoin('e.organisation', 'organisation', 'WITH', $with['with']);
            QueryHelper::setParameters($builder, $with['params']);
        }

        foreach ($context->sort as $key => $direction) {
            switch ($key) {
                case 'name':
                    $builder->addOrderBy('e.last_name', $direction);
                    $builder->addOrderBy('e.first_name', $direction);
                    break;

                default:
                    continue 2;
            }
            $context->sort->remove($key);
        }
    }
}
