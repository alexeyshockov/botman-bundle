<?php

namespace BotMan\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('botman');
        /** @var ArrayNodeDefinition $root */
        $root = $treeBuilder->getRootNode();

        $root
            ->children()
                ->arrayNode('slack')
                    ->children()
                        ->scalarNode('token')->isRequired()->end()
                    ->end()
                ->end()
                ->arrayNode('telegram')
                    ->children()
                        ->scalarNode('token')->isRequired()->end()
                    ->end()
                ->end()

                // See https://github.com/botman/studio/blob/master/config/botman/config.php
                ->scalarNode('conversation_cache_time')->defaultValue(30)->end()
                ->scalarNode('user_cache_time')->defaultValue(30)->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
