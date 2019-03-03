<?php

namespace BotMan\Bundle\Controller;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\Drivers\DriverManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WebHookController
{
    /** @var BotMan */
    private $botman;

    /** @var array */
    private $config;

    public function __construct(BotMan $botman, array $config, iterable $conversations)
    {
        $this->botman = $botman;
        $this->config = $config;

        // Force Symfony DI to create the objects
        foreach ($conversations as $conversation) {
        }
    }

    public function listenAction(Request $request)
    {
        if (DriverManager::verifyServices($this->config, $request)) {
            // Do nothing for such a request, the driver should have already responded
        } else {
            // TODO PR to support current request...
            $this->botman->listen();
        }

        // Empty response just to be a correct controller action (actual data was already sent sent by BotMan above)
        return Response::create();
    }
}
