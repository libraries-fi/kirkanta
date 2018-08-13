<?php

namespace UserAccountsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder;
        $root = $builder->root('user_accounts');
        $root
            ->children()
                ->scalarNode('user_entity')->end()
                ->arrayNode('template')
                    ->children()
                        ->scalarNode('login')
                            ->defaultValue('UserAccountsBundle:User:login.html.twig')
                        ->end()
                        ->scalarNode('register')
                            ->defaultValue('UserAccountsBundle:User:register.html.twig')
                        ->end()
                    ->end()
            ->end();

        return $builder;
    }
}
