<?php

namespace KirjastotFi\KirkantaApiBundle\Extension\Query;

use DomainException;
use Doctrine\ORM\QueryBuilder;
use KirjastotFi\KirkantaApiBundle\CompilerContext;
use App\Entity\Library;
use App\Entity\ServiceInstance;

class LibraryExtension implements QueryExtensionInterface
{
    public function supports(CompilerContext $context) : bool
    {
        return $context->getEntityClass() == Library::class;
    }

    public function build(QueryBuilder $builder, CompilerContext $context) : void
    {
        QueryHelper::filterCreated($builder, $context);
        QueryHelper::filterModified($builder, $context);
        QueryHelper::filterName($builder, $context);

        // $builder->addSelect('data');

        // $builder->join('e.address', 'address');
        // $builder->addSelect('address');

        // $builder->join('address.translations', 'address_data');
        // $builder->addSelect('address_data');

        /*
         * NOTE: This join is recommended for optimization as EntityNormalizer::entityId() will
         * check that the consortium is published, and that will trigger Doctrine to load all
         * accessed Consortium entities.
         */
        $builder->leftJoin('e.consortium', 'consortium');
        // $builder->addSelect('consortium');

        /*
         * When API query filters by city, these joins will be handled by BasicQueryCompiler.
         */
        if (!$context->params->has('city')) {
            // $builder->join('e.city', 'city');
            // $builder->addSelect('city');

            // $builder->join('city.translations', 'city_data');
            // $builder->addSelect('city_data');
        }

        /*
         * Eliminates additional queries when consortiums are fetched as refs.
         *
         * NOTE: Without langcode this join is actually slower than multiple separate queries.
         * Should check which one is better when there are many concurrent requests.
         *
         * FIXME: Could also just write a convenience function for adding these joins when langcode
         * is present.
         */
        if (!$context->params->has('consortium') && $context->refs->has('consortium')) {
            // $builder->join('consortium.translations', 'consortium_data');
            // $builder->addSelect('consortium_data');
        }

        if ($context->with->has('pictures')) {
            // $builder->innerJoin('e.pictures', 'pictures');
            // $builder->addSelect('pictures');
        }

        if ($context->with->has('mail_address')) {
            // $builder->innerJoin('e.mail_address', 'mail_address');
            // $builder->addSelect('mail_address');

            // NOTE: Again this join is slow at least without langcode parameter.
            // $builder->innerJoin('mail_address.translations', 'mail_address_data');
            // $builder->addSelect('mail_address_data');
        }

        if (($geo = $context->params->get('geo')) && isset($geo['pos'])) {
            $select = $builder->getDQLPart('select')[0];
            $select->add('ST_DISTANCE(address.coordinates, ST_GeomFromText(:geo_pos)) AS distance');

            $builder->join('e.address', 'address');
            $builder->andWhere('ST_DISTANCE(address.coordinates, ST_GeomFromText(:geo_pos)) < :geo_distance');
            $builder->setParameter('geo_pos', self::coordinatesToPostgis($geo['pos']));
            $builder->setParameter('geo_distance', $geo['dist'] * 1000);

            if (empty($context->sort)) {
                $context->sort['distance'] = 'asc';
            }
        }

        if ($with = QueryHelper::withForParam('service', $context)) {
            $builder->innerJoin(ServiceInstance::class, 'service_instance', 'WITH', 'service_instance MEMBER OF e.services');
            $builder->innerJoin('service_instance.template', 'service');
            $builder->innerJoin('service.translations', 'service_data', 'WITH', $with['with']);

            QueryHelper::setParameters($builder, $with['params']);
        }

        $context->params->remove('geo');

        foreach ($context->sort as $key => $direction) {
            switch ($key) {
                case 'city':
                    $builder->leftJoin('e.city', 'city');
                    $builder->leftJoin('city.translations', 'city_data', 'city_data.langcode = :langcode');
                    $builder->addOrderBy('city_data.name', $direction);
                    break;

                case 'consortium':
                    $builder->leftJoin('e.consortium', 'consortium');
                    $builder->addOrderBy('consortium.name', $direction);
                    break;

                case 'distance':
                    $builder->addOrderBy('distance', $direction);
                    break;

                default:
                    // Break out of the foreach loop also.
                    continue 2;
            }

            $context->sort->remove($key);
        }

        // exit;
    }

    public static function coordinatesToPostgis(string $input) : string
    {
        list($lat, $lon) = explode(',', $input);
        return sprintf('POINT(%2.6F %3.6F)', $lat, $lon);
    }
}
