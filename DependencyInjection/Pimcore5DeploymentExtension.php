<?php

namespace Pimcore5\DeploymentBundle\DependencyInjection;

use Pimcore5\DeploymentBundle\Config\Config;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class Pimcore5DeploymentExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
//        var_dump($configs);
//        die('configload');


        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);


//        var_dump($config);
//        die('cL2');
//        echo 'BEFORE';

        Config::set($config);
//        echo 'AFTER';

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
