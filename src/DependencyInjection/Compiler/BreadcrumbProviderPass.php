<?php

namespace App\DependencyInjection\Compiler;

use App\Menu\BreadcrumbBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class BreadcrumbProviderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container) : void
    {
        if (!$container->has(BreadcrumbBuilder::class)) {
            return;
        }

        $definition = $container->findDefinition(BreadcrumbBuilder::class);
        $tagged = $container->findTaggedServiceIds('app.breadcrumb_provider');
        $registered = [];

        foreach ($tagged as $id => $tags) {
            foreach ($tags as $tag) {
                /*
                 * $tags might contain duplicate entries as providers are tagged automatically based
                 * on their shared interface and explicit definitions in services.yaml are required
                 * to e.g. alter priority.
                 */

                $definition->addMethodCall('addProvider', [new Reference($id), $tag['priority'] ?? 0]);
                break;
            }
        }
    }
}
