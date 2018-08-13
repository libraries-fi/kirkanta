<?php

namespace KirjastotFi\KirkantaApiBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class KirjastotFiKirkantaApiExtension extends Extension
{
    public function load(array $config, ContainerBuilder $container)
    {
        $configuration = new Configuration;
        $processed = $this->processConfiguration($configuration, $config);
        $container->setParameter('kirkanta_api.entity_forms', $processed['entity_forms']);
        return $config;
    }
}
