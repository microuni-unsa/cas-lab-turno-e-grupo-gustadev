<?php

namespace idoit\Module\Report;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ReportExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $fileLoader = new YamlFileLoader($container, new FileLocator(\isys_module_report::getPath()));
        $fileLoader->load('config/services.yaml');
    }
}
