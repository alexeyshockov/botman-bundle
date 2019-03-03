<?php

namespace BotMan\Bundle\Command;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\Http\Curl;
use BotMan\Bundle\ConsoleDriver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel;

class MessageCommand extends Command
{
    /** @var BotMan */
    private $botman;

    /** @var Kernel */
    private $kernel;

    /** @var ConsoleDriver */
    private $driver;

    public function __construct(Kernel $kernel, BotMan $botman, ConsoleDriver $driver)
    {
        parent::__construct();

        $this->kernel = $kernel;
        $this->botman = $botman;
        $this->driver = $driver;

        $this->botman->setDriver($this->driver);
    }

    protected function configure()
    {
        $this
            ->setName('botman:message')
            ->setDescription('Handles an individual message')
            ->addArgument('message', InputArgument::REQUIRED, 'A message to process')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $input->getArgument('message');

        $request = new Request();
        $request->attributes->set('_controller', 'botman.webhook_controller::listenAction');

        $this->driver->initialize($message, $output);

        $this->kernel->handle($request, HttpKernelInterface::SUB_REQUEST);
    }
}
