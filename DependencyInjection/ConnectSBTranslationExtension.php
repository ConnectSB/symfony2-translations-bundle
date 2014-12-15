<?php

namespace ConnectSB\TranslationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ConnectSBTranslationExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        // Set the parameters which should be set in config.yml
        $container->setParameter('database_translations_domain', $configs[0]['database_translations_domain']);
        $container->setParameter('database_translations_entity', $configs[0]['database_translations_entity']);

        $loader->load('services.yml');
    }

    public function getAlias()
    {
        return 'connect_sb_translation';
    }
}
