<?php

namespace KirjastotFi\KirkantaApiBundle\Extension\Query;

use LogicException;
use Doctrine\ORM\QueryBuilder;
use KirjastotFi\KirkantaApiBundle\CompilerContext;
use App\Entity\Consortium;

class ConsortiumExtension implements QueryExtensionInterface
{
    public function supports(CompilerContext $context) : bool
    {
        return $context->getEntityClass() == Consortium::class;
    }

    public function build(QueryBuilder $builder, CompilerContext $context) : void
    {
        QueryHelper::filterCreated($builder, $context);
        QueryHelper::filterModified($builder, $context);
        QueryHelper::filterName($builder, $context);

        if ($finna_id = $context->params->get('finna:id')) {
            if (!$context->params->get('finna:special')) {
                throw new LogicException('Parameter \'finna:id\' cannot be used without \'finna:special\'');
            }

            $context->params->remove('finna:id');

            $builder->innerJoin('e.finna_data', 'finna');
            $builder->andWhere('finna.finna_id = :finna_id');
            $builder->setParameter('finna_id', $finna_id);
        }

        if ($context->params->has('finna:special')) {
            $state = $context->params->get('finna:special');
            $context->params->remove('finna:special');

            if ($state !== 'any') {
                $builder->leftJoin('e.finna_data', 'finna');
                $builder->andWhere('COALESCE(finna.special, FALSE) = :finna_special');
                $builder->setParameter('finna_special', $state);
            }
        }
    }
}
