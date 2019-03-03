<?php

namespace BotMan\Bundle\DependencyInjection;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\Cache\Psr6Cache;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\Http\Curl;
use BotMan\BotMan\Interfaces\CacheInterface;
use BotMan\BotMan\Interfaces\HttpInterface;
use BotMan\BotMan\Interfaces\StorageInterface;
use BotMan\Bundle\BotManFactory;
use BotMan\Bundle\Command\ChatCommand;
use BotMan\Bundle\Command\MessageCommand;
use BotMan\Bundle\ConsoleDriver;
use BotMan\Bundle\Controller\WebHookController;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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

        $container->register(BotManFactory::class)
            ->addArgument($config)
            ->addArgument(new Reference(ContainerInterface::class))
        ;

        $container->register(CacheInterface::class)
            ->setClass(Psr6Cache::class)
            ->addArgument(new Reference(CacheItemPoolInterface::class))
        ;
        $container->register(StorageInterface::class)
            ->setFactory([new Reference(BotManFactory::class), 'createFileStorage'])
            ->addArgument(new Parameter('kernel.cache_dir'))
        ;
        $container->register(HttpInterface::class, Curl::class);
        $container->register(DriverManager::class)
            ->addArgument($config)
            ->addArgument(new Reference(HttpInterface::class))
        ;

        $container->register(BotMan::class)
            ->setFactory([new Reference(BotManFactory::class), 'create'])
            ->addArgument(new Reference(DriverManager::class))
            ->addArgument(new Reference(RequestStack::class))
            ->addArgument(new Reference(CacheInterface::class))
            ->addArgument(new Reference(StorageInterface::class))
        ;

        $container->register(ConsoleDriver::class);

        $container->register('botman.webhook_controller', WebHookController::class)
            ->addArgument(new Reference(BotMan::class))
            ->addArgument($config)
            // This third argument is needed only to actually create all these services. From Symfony docs: "But if you
            // never ask for the service, it's never constructed: saving memory and speed". So this ensures that all
            // the object are actually created.
            ->addArgument(new TaggedIteratorArgument('botman.conversation'))
            ->addTag('controller.service_arguments')
        ;
        $container->register('botman.chat_command', ChatCommand::class)
            ->addTag('console.command')
        ;
        $container->register('botman.message_command', MessageCommand::class)
            ->addArgument(new Reference(KernelInterface::class))
            ->addArgument(new Reference(BotMan::class))
            ->addArgument(new Reference(ConsoleDriver::class))
            ->addTag('console.command')
        ;
    }
}
