<?php

namespace KirjastotFi\KirkantaApiBundle\Extension\Query;

use Doctrine\ORM\QueryBuilder;
use KirjastotFi\KirkantaApiBundle\CompilerContext;

class QueryHelper
{
    const FALLBACK_LANGCODE = 'fi';

    /**
     * Generate a WITH clause to be used with JOINs.
     */
    public static function withForParam(string $name, CompilerContext $context) : ?array
    {
        if ($data = $context->params->get($name)) {
            $context->params->remove($name);

            $langcode = self::langcode($context, true);
            $value = reset($data);
            $key = key($data);

            switch ($key) {
                // case 'id':
                //     $param = "{$name}_id";
                //     return [
                //         'with' => "{$name} = :$param",
                //         'params' => [$param => $value]
                //     ];

                case 'id':
                    $param = "{$name}_id";
                    return [
                        'with' => "{$name}_data.entity = :$param AND {$name}_data.langcode = :langcode",
                        'params' => [
                            'langcode' => $langcode,
                            $param => $value
                        ]
                    ];

                case 'slug':
                    $param = "{$name}_slug";
                    return [
                        'with' => "TRANS(:langcode, {$name}_data.slug) = :{$param}",
                        'params' => [
                            'langcode' => $langcode,
                            $param => $value
                        ],
                    ];

                case 'name':
                    $param = "{$name}_name";
                    return [
                        'with' => "LOWER(TRANS(:langcode, {$name}_data.name)) LIKE :{$param}",
                        'params' => [
                            'langcode' => $langcode,
                            $param => "%{$value}%"
                        ],
                    ];
            }
        } else {
            return null;
        }
    }

    /**
     * Helper for adding multiple parameters into the query.
     */
    public static function setParameters(QueryBuilder $builder, array $params) : void
    {
        foreach ($params as $key => $value) {
            $builder->setParameter($key, $value);
        }
    }

    /**
     * Return language used in the query.
     *
     * Will return NULL if no language defined in query.
     *
     * @param $fallback If set to true and no language defined in query, will return the fallback language instead of NULL.
     */
    public static function langcode(CompilerContext $context, $fallback = false) : ?string
    {
        if ($context->langcode) {
            return $context->langcode;
        } else if ($fallback) {
            return self::FALLBACK_LANGCODE;
        } else {
            return null;
        }
    }

    public static function filterCreated(QueryBuilder $builder, CompilerContext $context) : void
    {
        if ($created = $context->params->get('created')) {
            $context->params->remove('created');
            $value = reset($created);
            $operator = key($created) == 'after' ? '>=' : '<=';
            $builder->andWhere(sprintf('e.created %s :created', $operator));
            $builder->setParameter('created', $value->format('c'));

            if (empty($context->sort)) {
                $context->sort['created'] = key($created) == 'after' ? 'asc' : 'desc';
            }
        }
    }

    public static function filterModified(QueryBuilder $builder, CompilerContext $context) : void
    {
        if ($modified = $context->params->get('modified')) {
            $context->params->remove('modified');
            $value = reset($modified);
            $operator = key($modified) == 'after' ? '>=' : '<=';
            $builder->andWhere(sprintf('e.modified %s :modified', $operator));
            $builder->setParameter('modified', $value->format('c'));

            if (!count($context->sort)) {
                $context->sort->set('modified', key($modified) == 'after' ? 'asc' : 'desc');
            }
        }
    }

    public static function filterName(QueryBuilder $builder, CompilerContext $context) : void
    {
        if ($name = $context->params->get('name')) {
            $context->params->remove('name');
            $langcode = $context->langcode ?? self::FALLBACK_LANGCODE;
            $builder->andWhere('LOWER(TRANS(:langcode, data.name)) LIKE :name');
            $builder->setParameter('langcode', $langcode);
            $builder->setParameter('name', "%{$name}%");
        }
    }
}
