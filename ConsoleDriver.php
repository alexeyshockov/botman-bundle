<?php

namespace BotMan\Bundle;

use BotMan\BotMan\Interfaces\DriverInterface;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Users\User;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Simple driver for Symfony Console, to test a bot locally
 */
class ConsoleDriver implements DriverInterface
{
    const DRIVER_NAME = 'Symfony Console';

    private $botName = 'Bot';

    private $message;

    /** @var OutputInterface */
    private $output;

    /** @var bool */
    private $hasQuestion = false;

    /** @var array */
    private $lastQuestions;

    public function setBotName($name)
    {
        $this->botName = $name;

        return $this;
    }

    public function initialize($message, OutputInterface $output)
    {
        $this->message = $message;
        $this->output = $output;
    }

    public function getName()
    {
        return self::DRIVER_NAME;
    }

    public function matchesRequest()
    {
        return false;
    }

    public function getConversationAnswer(IncomingMessage $message)
    {
        $index = (int) $message->getText() - 1;

        if ($this->hasQuestion && isset($this->lastQuestions[$index])) {
            $question = $this->lastQuestions[$index];

            return Answer::create($question['name'])
                ->setInteractiveReply(true)
                ->setValue($question['value'])
                ->setMessage($message);
        }

        return Answer::create($this->message)->setMessage($message);
    }

    public function getMessages()
    {
        return [new IncomingMessage($this->message, 999, '#channel', $this->message)];
    }

    public function isBot()
    {
        return strpos($this->message, $this->botName . ': ') === 0;
    }

    public function types(IncomingMessage $matchingMessage)
    {
        $this->output->writeln($this->botName . ': ...');
    }

    public function getUser(IncomingMessage $matchingMessage)
    {
        return new User($matchingMessage->getSender());
    }

    public function isConfigured()
    {
        return false;
    }

    public function buildServicePayload($message, $matchingMessage, $additionalParameters = [])
    {
        $questionData = null;
        if ($message instanceof OutgoingMessage) {
            $text = $message->getText();
        } elseif ($message instanceof Question) {
            $text = $message->getText();
            $questionData = $message->toArray();
        } else {
            $text = $message;
        }

        return compact('text', 'questionData');
    }

    public function sendPayload($payload)
    {
        $questionData = $payload['questionData'];
        $this->output->writeln($this->botName . ': ' . $payload['text']);

        if (!is_null($questionData)) {
            foreach ($questionData['actions'] as $key => $action) {
                $this->output->writeln(($key + 1) . ') ' . $action['text']);
            }
            $this->hasQuestion = true;
            $this->lastQuestions = $questionData['actions'];
        }
    }

    /**
     * Does the driver match to an incoming messaging service event.
     *
     * @return bool|mixed
     */
    public function hasMatchingEvent()
    {
        return false;
    }

    /**
     * Tells if the stored conversation callbacks are serialized.
     *
     * @return bool
     */
    public function serializesCallbacks()
    {
        return false;
    }
}
