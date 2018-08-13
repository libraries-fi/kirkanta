<?php

namespace KirjastotFi\KirkantaApiBundle\Extension\Query;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use KirjastotFi\KirkantaApiBundle\CompilerContext;
use App\Entity\Feature\Translatable;

class QueryTranslationCompiler implements QueryExtensionInterface
{
    protected $entity_manager;

    public function __construct(EntityManagerInterface $entity_manager)
    {
        $this->entity_manager = $entity_manager;
    }

    public function supports(CompilerContext $context) : bool
    {
        return is_a($context->getEntityClass(), Translatable::class, true);
    }

    public function build(QueryBuilder $builder, CompilerContext $context) : void
    {
        $metadata = $this->entity_manager->getClassMetadata($context->getEntityClass() . 'Data');

        foreach ($context->params as $key => $data) {
            // NOTE: As of now the translation data class won't have associations.

            if (isset($metadata->fieldMappings[$key])) {
                if (is_array($data)) {
                    $builder->andWhere(sprintf('data.%s IN (:%s)', $key, $key));
                    $builder->setParameter($key, $data);
                } else {
                    $builder->andWhere(sprintf('data.%s = :%s', $key, $key));
                    $builder->setParameter($key, $data);
                }
                $context->params->remove($key);
            }
        }

        foreach ($context->sort as $key => $direction) {
            if (isset($metadata->fieldMappings[$key])) {
                // $builder->addGroupBy("data.{$key}");
                $builder->addSelect("data.{$key}");
                $builder->addOrderBy("data.{$key}", $direction);
                $context->sort->remove($key);
            }
        }
    }
}
