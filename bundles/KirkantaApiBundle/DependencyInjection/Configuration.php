<?php

namespace KirjastotFi\KirkantaApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder;
        $root = $builder->root('kirkanta_api');
        $root
            ->children()
                ->arrayNode('entity_types')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('entity_forms')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('get')->end()
                            ->scalarNode('post')->end()
                        ->end()
                    ->end()
            ->end();

        return $builder;
    }
}
