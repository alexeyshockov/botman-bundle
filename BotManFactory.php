<?php

namespace BotMan\Bundle;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\Interfaces\CacheInterface;
use BotMan\BotMan\Interfaces\StorageInterface;
use BotMan\BotMan\Storages\Drivers\FileStorage;
use BotMan\Drivers\Slack\SlackDriver;
use BotMan\Drivers\Telegram\TelegramAudioDriver;
use BotMan\Drivers\Telegram\TelegramDriver;
use BotMan\Drivers\Telegram\TelegramFileDriver;
use BotMan\Drivers\Telegram\TelegramLocationDriver;
use BotMan\Drivers\Telegram\TelegramPhotoDriver;
use BotMan\Drivers\Telegram\TelegramVideoDriver;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class BotManFactory
{
    private $config;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(array $config, ContainerInterface $container)
    {
        $this->config = $config;
        $this->container = $container;

        $this->loadDrivers($this->config);
    }

    public function loadDrivers(array $config)
    {
        if (array_key_exists('slack', $config)) {
            DriverManager::loadDriver(SlackDriver::class);
        }
        if (array_key_exists('telegram', $config)) {
            DriverManager::loadDriver(TelegramDriver::class);
            DriverManager::loadDriver(TelegramAudioDriver::class);
            DriverManager::loadDriver(TelegramFileDriver::class);
            DriverManager::loadDriver(TelegramLocationDriver::class);
            DriverManager::loadDriver(TelegramPhotoDriver::class);
            DriverManager::loadDriver(TelegramVideoDriver::class);
        }
    }

    public function create(
        DriverManager $driverManager,
        RequestStack $requestStack,
        CacheInterface $cache,
        StorageInterface $storageDriver
    )
    {
        // Request in unavailable in CLI, but BotMan requires it. This is a hack, kinda.
        $driver = $driverManager->getMatchingDriver(
            $requestStack->getCurrentRequest() ?? Request::createFromGlobals()
        );

        $botman = new BotMan($cache, $driver, $this->config, $storageDriver);
        $botman->setContainer($this->container);

        return $botman;
    }

    public function createFileStorage($path)
    {
        return new FileStorage($path . DIRECTORY_SEPARATOR . 'botman');
    }
}
