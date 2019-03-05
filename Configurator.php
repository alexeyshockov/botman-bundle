<?php

namespace BotMan\Bundle;

use AlexS\BotMan\Annotations\Processor;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\Interfaces\Middleware\Captured;
use BotMan\BotMan\Interfaces\Middleware\Heard;
use BotMan\BotMan\Interfaces\Middleware\Matching;
use BotMan\BotMan\Interfaces\Middleware\Received;
use BotMan\BotMan\Interfaces\Middleware\Sending;
use LogicException;

class Configurator
{
    /** @var Processor|null */
    private $annotationProcessor;

    /** @var object[] */
    private $configurators = [];

    /** @var object[] */
    private $middlewares = [];

    public function __construct(Processor $annotationProcessor = null, iterable $configurators, iterable $middlewares)
    {
        $this->annotationProcessor = $annotationProcessor;
        $this->configurators = is_array($configurators) ? $configurators : iterator_to_array($configurators);
        $this->middlewares = is_array($middlewares) ? $middlewares : iterator_to_array($middlewares);
    }

    public function configure(BotMan $bot)
    {
        $this->configureMiddlewares($bot);
        $this->configureFromAnnotations($bot);
    }

    private function configureFromAnnotations(BotMan $bot)
    {
        if (!$this->annotationProcessor) {
            return;
        }

        foreach ($this->configurators as $configurator) {
            $this->annotationProcessor->add($configurator);
        }

        $this->annotationProcessor->applyTo($bot);
    }

    private function configureMiddlewares(BotMan $bot)
    {
        foreach ($this->middlewares as $middleware) {
            $matched = false;
            if ($middleware instanceof Captured) {
                $matched = true;
                $bot->middleware->captured($middleware);
            }
            if ($middleware instanceof Heard) {
                $matched = true;
                $bot->middleware->heard($middleware);
            }
            if ($middleware instanceof Matching) {
                $matched = true;
                $bot->middleware->matching($middleware);
            }
            if ($middleware instanceof Received) {
                $matched = true;
                $bot->middleware->received($middleware);
            }
            if ($middleware instanceof Sending) {
                $matched = true;
                $bot->middleware->sending($middleware);
            }

            if (!$matched) {
                throw new LogicException(
                    'A BotMan middleware must implement one of BotMan\\BotMan\\Interfaces\\Middleware\\* interfaces'
                );
            }
        }
    }
}
