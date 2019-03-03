<?php

namespace BotMan\Bundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class ChatCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('botman:chat')
            ->setDescription('Chat with your bot from a terminal, easy')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        while (true) {
            $question = new Question('You: ');
            $message = $helper->ask($input, $output, $question);
            $this->processMessage($message, $output);
        }
    }

    private function processMessage($message, $output)
    {
        $command = $this->getApplication()->find('botman:message');

        $arguments = [
            'command' => 'botman:message',
            'message' => $message,
        ];

        return $command->run(new ArrayInput($arguments), $output);
    }
}
