<?php

namespace App\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class KirjastotFiKirkantaExtension extends Extension
{
    public function load(array $config, ContainerBuilder $container)
    {
        $configuration = new Configuration;
        $processed = $this->processConfiguration($configuration, $config);

        $container->setParameter('kirkanta.entity_types', $processed['entity_types']);
        $container->setParameter('kirkanta.content_languages', $processed['content_languages']);
        return $config;
    }
}
