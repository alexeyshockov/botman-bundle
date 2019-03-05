<?php

namespace BotMan\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('botman');
        /** @var ArrayNodeDefinition $root */
        $root = $treeBuilder->getRootNode();

        $root->children()
            // See https://github.com/botman/studio/blob/master/config/botman/config.php
            ->scalarNode('conversation_cache_time')->defaultValue(30)->end()
            ->scalarNode('user_cache_time')->defaultValue(30)->end()
        ->end();

        $this->addSlack($root->children());
        $this->addTelegram($root->children());

        // TODO Add other common drivers

        return $treeBuilder;
    }

    private function addSlack(NodeBuilder $builder)
    {
        $builder->arrayNode('slack')
            ->children()
                ->scalarNode('token')->isRequired()->end()
            ->end()
        ->end();
    }

    private function addTelegram(NodeBuilder $builder)
    {
        $builder->arrayNode('telegram')
            ->children()
                ->scalarNode('token')->isRequired()->end()
            ->end()
        ->end();
    }
}
