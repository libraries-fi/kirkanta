<?php

namespace UserAccountsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class UserAccountsExtension extends Extension
{
    public function load(array $config, ContainerBuilder $container)
    {
        $configuration = new Configuration;
        $config = $this->processConfiguration($configuration, $config);

        $container->setParameter('user_accounts.user_entity', $config['user_entity'] ?? null);
        $container->setParameter('user_accounts.template.login', $config['template']['login'] ?? null);
        $container->setParameter('user_accounts.template.register', $config['template']['register'] ?? null);

        return $config;
    }
}
