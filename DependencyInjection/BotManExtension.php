<?php

namespace BotMan\Bundle\DependencyInjection;

use AlexS\BotMan\Annotations\Processor;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Cache\Psr6Cache;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\Http\Curl;
use BotMan\BotMan\Interfaces\CacheInterface;
use BotMan\BotMan\Interfaces\HttpInterface;
use BotMan\BotMan\Interfaces\StorageInterface;
use BotMan\Bundle\Factory;
use BotMan\Bundle\Command\ChatCommand;
use BotMan\Bundle\Command\MessageCommand;
use BotMan\Bundle\Configurator;
use BotMan\Bundle\ConsoleDriver;
use BotMan\Bundle\Controller\WebHookController;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;

class BotManExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $container->register(CacheInterface::class)
            ->setClass(Psr6Cache::class)
            ->addArgument(new Reference(CacheItemPoolInterface::class))
        ;
        $container->register(StorageInterface::class)
            ->setFactory([new Reference(Factory::class), 'createFileStorage'])
            ->addArgument(new Parameter('kernel.cache_dir'))
        ;
        $container->register(HttpInterface::class, Curl::class);
        $container->register(DriverManager::class)
            ->addArgument($config)
            ->addArgument(new Reference(HttpInterface::class))
        ;

        $container->register(Factory::class)
            ->addArgument($config)
            ->addArgument(new Reference(PsrContainerInterface::class))
            ->addArgument(new Reference(RequestStack::class))
        ;
        $container->register(Configurator::class)
            ->setPublic(true)
            ->addArgument(new Reference(Processor::class, ContainerInterface::NULL_ON_INVALID_REFERENCE))
            ->addArgument(new TaggedIteratorArgument('botman.configurator'))
            ->addArgument(new TaggedIteratorArgument('botman.middleware'))
        ;
        $container->register(BotMan::class)
            ->setFactory([new Reference(Factory::class), 'create'])
            ->addArgument(new Reference(DriverManager::class))
            ->addArgument(new Reference(CacheInterface::class))
            ->addArgument(new Reference(StorageInterface::class))
            ->setPublic(true)
            ->setConfigurator([new Reference(Configurator::class), 'configure'])
        ;
        $container->setAlias('botman', BotMan::class)->setPublic(true);

        $container->register(WebHookController::class)
            ->addArgument(new Reference(BotMan::class))
            ->addArgument($config)
            ->setPublic(true)
            ->addTag('controller.service_arguments')
        ;

        $container->register(ConsoleDriver::class);

        $container->register(ChatCommand::class)
            ->addTag('console.command')
            // Expose to public, otherwise Symfony will create a public alias anyway
            ->setPublic(true)
        ;
        $container->register(MessageCommand::class)
            ->addArgument(new Reference(KernelInterface::class))
            ->addArgument(new Reference(BotMan::class))
            ->addArgument(new Reference(ConsoleDriver::class))
            ->addTag('console.command')
            // Expose to public, otherwise Symfony will create a public alias anyway
            ->setPublic(true)
        ;

        $this->loadAnnotations($configs, $container);
    }

    private function loadAnnotations(array $configs, ContainerBuilder $container)
    {
        // If alexeyshockov/botman-annotations is not available, simply skip this step
        if (!class_exists(Processor::class)) {
            return;
        }

        $container->register(Processor::class)
            ->addArgument(new Reference('annotations.reader'))
        ;
    }
}
