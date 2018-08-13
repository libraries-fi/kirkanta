<?php

namespace App\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder;
        $root = $builder->root('kirkanta');
        $root->children()
            ->arrayNode('content_languages')
                ->prototype('scalar')->end()
            ->end()
            ->arrayNode('entity_types')
                ->prototype('array')->children()
                    ->scalarNode('id')->end()
                    ->scalarNode('label')->end()
                    ->scalarNode('label_multiple')->end()
                    ->scalarNode('class_name')->end()
                    ->scalarNode('list_builder')->end()
                    ->arrayNode('forms')->prototype('scalar')->end()

            ->end()
        ->end();

        return $builder;
    }
}
