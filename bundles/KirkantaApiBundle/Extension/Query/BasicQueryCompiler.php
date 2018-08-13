<?php

namespace KirjastotFi\KirkantaApiBundle\Extension\Query;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use KirjastotFi\KirkantaApiBundle\CompilerContext;
use App\Entity\Feature\StateAwareness;

/**
 * Handles generic use-cases for filtering, joining etc.
 *
 * This extension is executed last, so any overrides can be put into type-specific
 * extensions.
 */
class BasicQueryCompiler implements QueryExtensionInterface
{
    protected $entity_manager;

    public function __construct(EntityManagerInterface $entity_manager)
    {
        $this->entity_manager = $entity_manager;
    }

    public function supports(CompilerContext $context) : bool
    {
        return true;
    }

    public function build(QueryBuilder $builder, CompilerContext $context) : void
    {
        $metadata = $this->entity_manager->getClassMetadata($context->getEntityClass());

        if (is_a($context->getEntityClass(), StateAwareness::class, true)) {
            $builder->andWhere('e.state = :published');
            $builder->setParameter('published', StateAwareness::PUBLISHED);
        }

        foreach ($context->params as $key => $data) {
            if (isset($metadata->fieldMappings[$key])) {
                if (is_array($data)) {
                    $builder->andWhere(sprintf('e.%s IN (:%s)', $key, $key));
                    $builder->setParameter($key, $data);
                } else {
                    $builder->andWhere(sprintf('e.%s = :%s', $key, $key));
                    $builder->setParameter($key, $data);
                }
                $context->params->remove($key);
            } else if (isset($metadata->associationMappings[$key])) {
                $association_class = $metadata->associationMappings[$key]['targetEntity'];
                $association_meta = $this->entity_manager->getClassMetadata($association_class);

                foreach ($data as $ckey => $cdata) {
                    if (is_array($cdata)) {
                        $cdata = array_map('mb_strtolower', $cdata);
                    } else {
                        $cdata = mb_strtolower($cdata);
                    }
                    $join_alias = sprintf('%s', $key);
                    $param_alias = $join_alias . $ckey;
                    $builder->innerJoin(sprintf('e.%s', $key), $join_alias);

                    if (isset($association_meta->fieldMappings[$ckey])) {
                        $type = $association_meta->fieldMappings[$ckey]['type'];

                        if ($type == 'integer') {
                            $builder->andWhere(sprintf('%s.%s = :%s', $join_alias, $ckey, $param_alias));
                        } else {
                            $builder->andWhere(sprintf('LOWER(%s.%s) = :%s', $join_alias, $ckey, $param_alias));
                        }
                        $builder->setParameter($param_alias, $cdata);
                    } else {
                        // Guess that we need to filter by a translated property.
                        $langcode = QueryHelper::langcode($context, true);

                        $cjoin_alias = sprintf('%s_data', $join_alias);
                        $builder->innerJoin(sprintf('%s.translations', $join_alias), $cjoin_alias, 'WITH', sprintf('%s.langcode = :langcode', $cjoin_alias));
                        $builder->andWhere(sprintf('LOWER(%s.%s) = :%s', $cjoin_alias, $ckey, $param_alias));
                        $builder->setParameter($param_alias, $cdata);
                        $builder->setParameter('langcode', $langcode);
                    }

                }
                $context->params->remove($key);
            }
        }

        foreach ($context->refs as $field => $key) {
            if (isset($metadata->associationMappings[$field])) {
                $builder->leftJoin("e.{$field}", $field . $key);
                // $builder->addSelect($field . $key);
                $context->refs->remove($field);
            }
        }

        foreach ($context->sort as $key => $direction) {
            if (isset($metadata->fieldMappings[$key])) {
                $builder->addSelect("e.{$key}");
                $builder->addOrderBy("e.{$key}", $direction);
                $context->sort->remove($key);
            }
        }
    }
}
