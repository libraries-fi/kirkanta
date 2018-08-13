<?php

namespace KirjastotFi\KirkantaApiBundle;

use KirjastotFi\KirkantaApiBundle\DependencyInjection\Compiler\QueryCompilerExtensionPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class KirjastotFiKirkantaApiBundle extends Bundle
{
    public function build(ContainerBuilder $container) : void
    {
        $container->addCompilerPass(new QueryCompilerExtensionPass);
    }
}
