<?php

namespace Btn\MessageBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('btn_message');

        $rootNode
            ->children()
                ->scalarNode('thread_class')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('message_class')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('message_manager')
                    ->defaultValue('btn_message.message_manager.default')
                    ->cannotBeEmpty()
                    ->end()
                ->scalarNode('thread_manager')
                    ->defaultValue('btn_message.thread_manager.default')
                    ->cannotBeEmpty()
                    ->end()
                ->arrayNode('message_type')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('id')->isRequired()->end()
                            ->scalarNode('name')->end()
                        ->end()
                    ->end()
                ->end()
        ;

        return $treeBuilder;
    }
}
