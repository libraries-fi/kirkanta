<?php

namespace KirjastotFi\KirkantaApiBundle\DependencyInjection\Compiler;

use KirjastotFi\KirkantaApiBundle\QueryCompiler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class QueryCompilerExtensionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container) : void
    {
        if (!$container->has('kirkanta_api.query_compiler')) {
            return;
        }

        $definition = $container->findDefinition('kirkanta_api.query_compiler');
        $tagged = $container->findTaggedServiceIds('kirkanta_api.query_extension');

        foreach ($tagged as $id => $options) {
            $priority = $options[0]['priority'] ?? 0;
            $definition->addMethodCall('addExtension', [new Reference($id), $priority]);
        }
    }
}
