<?php

namespace KirjastotFi\KirkantaApiBundle;

use DomainException;
use SplPriorityQueue;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use KirjastotFi\KirkantaApiBundle\CompilerContext;
use KirjastotFi\KirkantaApiBundle\Extension\Query\QueryExtensionInterface;
use App\Entity\Feature\StateAwareness;
use App\Entity\Feature\Translatable;
use App\EntityTypeManager;

class QueryCompiler
{
    private $entityManager;
    private $entityTypeManager;
    private $extensions;

    public function __construct(EntityManagerInterface $entityManager, EntityTypeManager $typeManager)
    {
        $this->entityManager = $entityManager;
        $this->entityTypeManager = $typeManager;
        $this->extensions = new SplPriorityQueue;
    }

    public static function isArrayList(array $data) : bool
    {
        return array_key_exists(0, $data);
    }

    public function addExtension(QueryExtensionInterface $extension, int $priority = 0) : void
    {
        $this->extensions->insert($extension, $priority);
    }

    public function compile(string $type, array $values, string $langcode = null) : QueryBuilder
    {
        $entity_class = $this->entityTypeManager->getEntityClass($type);
        $values = $this->filter($values);

        /*
         * NOTE: Using our extended QueryBuilder class here, not the default one from Doctrine.
         *
         * NOTE: Select with alias to ensure that the entity is always in a fixed index.
         * With certain queries like GEO we need to add virtual properties to the result
         * for convenience.
         */
        $builder = (new QueryBuilder($this->entityManager))
            // ->select('e AS entity')
            ->select(['e.id', 'e.cached_document'])
            ->from($entity_class, 'e', 'e.id')
            // ->distinct('e.id')
            ;

        if (is_a($entity_class, Translatable::class, true)) {
            if ($langcode) {
                $builder->join('e.translations', 'data', 'WITH', 'data.langcode = :langcode');
                $builder->setParameter('langcode', $langcode);
            } else {
                $builder->join('e.translations', 'data');
            }
        }

        $refs = $values['refs'] ?? [];
        $with = $values['with'] ?? [];
        $sort = [];

        foreach ($values['sort'] as $param) {
            if ($param[0] == '-') {
                $sort[substr($param, 1)] = 'desc';
            } else {
                $sort[$param] = 'asc';
            }
        }

        unset($values['sort']);
        unset($values['refs']);
        unset($values['with']);

        unset($values['scope']);

        $context = new CompilerContext($entity_class, $langcode, $values, $sort, $refs, $with);

        foreach ($this->extensions as $extension) {
            if ($extension->supports($context)) {
                $extension->build($builder, $context);
            }
        }

        if (count($context->params) > 0) {
            throw new DomainException(sprintf('QueryCompiler was not able to process all parameters: \'%s\'', implode('\', \'', $context->params->keys())));
        }

        if (count($context->sort) > 0) {
            throw new DomainException(sprintf('QueryCompiler was not able to process all sorting parameters: \'%s\'', implode('\', \'', $context->sort->keys())));
        }

        if (count($context->refs) > 0) {
            throw new DomainException(sprintf('QueryCompiler was not able to process all refs parameters: \'%s\'', implode('\', \'', $context->refs->all())));
        }

        $builder->setMaxResults(10);

        return $builder;
        return $builder->getQuery();
    }

    protected function filter(array $data) : array
    {
        $filtered = [];

        foreach ($data as $key => $data) {
            if (is_array($data)) {
                $data = $this->filter($data);
            }

            if (!is_null($data) && (!is_array($data) || !empty($data))) {
                $filtered[$key] = $data;
            }
        }
        return $filtered;
    }
}
