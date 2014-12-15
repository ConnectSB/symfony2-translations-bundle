<?php

namespace ConnectSB\TranslationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('connect_sb_translation');

        $notSupportedDomains = array('messages');

        $rootNode
            ->children()
            ->scalarNode('database_translations_domain')
            ->cannotBeEmpty()
            ->validate()
            ->ifInArray($notSupportedDomains)
            ->thenInvalid('The domain to get the translations from can\'t be messages.')
            ->end()
            ->end()
            ->scalarNode('database_translations_entity')
            ->cannotBeEmpty()
            ->end();

        return $treeBuilder;
    }
}
