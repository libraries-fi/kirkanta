<?php

namespace KirjastotFi\KirkantaApiBundle\Extension\Query;

use Doctrine\ORM\QueryBuilder;
use KirjastotFi\KirkantaApiBundle\CompilerContext;
use App\Entity\Service;
use App\Entity\ServiceInstance;

class ServiceExtension implements QueryExtensionInterface
{
    public function supports(CompilerContext $context) : bool
    {
        return $context->getEntityClass() == Service::class;
    }

    public function build(QueryBuilder $builder, CompilerContext $context) : void
    {
        QueryHelper::filterCreated($builder, $context);
        QueryHelper::filterModified($builder, $context);
        QueryHelper::filterName($builder, $context);

        if ($with = QueryHelper::withForParam('consortium', $context)) {
            $builder->innerJoin(ServiceInstance::class, 'service', 'WITH', 'service MEMBER OF e.instances');
            $builder->innerJoin('service.library', 'library');
            $builder->innerJoin('library.consortium', 'consortium');
            $builder->innerJoin('consortium.translations', 'consortium_data', 'WITH', $with['with']);

            QueryHelper::setParameters($builder, $with['params']);
        }

        if ($with = QueryHelper::withForParam('city', $context)) {
            $builder->innerJoin(ServiceInstance::class, 'service', 'WITH', 'service MEMBER OF e.instances');
            $builder->innerJoin('service.library', 'library');
            $builder->innerJoin('library.city', 'city');
            $builder->innerJoin('city.translations', 'city_data', 'WITH', $with['with']);

            QueryHelper::setParameters($builder, $with['params']);
        }
    }
}
