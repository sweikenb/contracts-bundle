<?php

namespace Sweikenb\Bundle\Contracts\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SweikenbContractsExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter(
            sprintf("%s.%s", Configuration::PREFIX, Configuration::LOCKFILE),
            sprintf(
                "%%kernel.project_dir%%/%s",
                trim($config[Configuration::LOCKFILE] ?? 'contracts.lock', '/')
            )
        );

        $container->setParameter(
            sprintf("%s.%s", Configuration::PREFIX, Configuration::ON_UNEXPECTED_CHANGE),
            $config[Configuration::ON_UNEXPECTED_CHANGE] ?? 'fail'
        );

        $container->setParameter(
            sprintf("%s.%s", Configuration::PREFIX, Configuration::SCAN_DIRS),
            array_map(
                fn(string $path) => sprintf("%%kernel.project_dir%%/%s", trim($path, '/')),
                $config[Configuration::SCAN_DIRS] ?? []
            )
        );

        $container->setParameter(
            sprintf("%s.%s", Configuration::PREFIX, Configuration::SCAN_FILE_PATTERNS),
            $config[Configuration::SCAN_FILE_PATTERNS] ?? []
        );

        $container->setParameter(
            sprintf("%s.%s", Configuration::PREFIX, Configuration::SCAN_IGNORE_FILE_PATTERNS),
            $config[Configuration::SCAN_IGNORE_FILE_PATTERNS] ?? []
        );

        $container->setParameter(
            sprintf("%s.%s", Configuration::PREFIX, Configuration::TRIGGERS),
            $config[Configuration::TRIGGERS] ?? []
        );
    }
}
