# BotMan integration for Symfony Framework

The bundle simplifies usage of BotMan in a Symfony application.

## Installation

```
composer install alexeyshockov/botman-bundle
```

## Configuring drivers

BotMan supports a lot of different messengers via drivers. You can enable a specific driver via 
`DriverManager::loadDriver()` manually, of course, but the bundle already contains convenient configuration options for 
most of the drivers.

### Slack
```yaml
# botman.yaml
botman:
  slack:
    token: <YOUR_SLACK_TOKEN>
```

### Telegram
```yaml
# botman.yaml
botman:
  telegram:
    token: <YOUR_TELEGRAM_TOKEN>
```

## Configuring the bot in your application

After you enable the bundle in your app, BotMan service will be available in the DI container immediately. You can 
access it by the FQCN (\Botman\Botman) or by `botman` alias.  

To configure the bot there are two options are available.

### Using bot configurator services

You can define a service and mark it as a bot configurator with `botman.configurator` tag.

Usually it's useful to put this tag on all services in a specific namespace, like for web controllers:

```yaml
# services.yaml
services:
  App\Conversation\:
    resource: '../src/Conversation/*'
    tags: ['botman.configurator']
``` 

The you can just get a BotMan instance with autowiring and configure it:

```php
use AlexS\BotMan\Annotations\Hears;
use BotMan\BotMan\BotMan;

class GreetingConversation
{
    public function __construct(BotMan $bot)
    {
        $bot->hears('Hi', function ($bot) {
            $bot->reply('Hi! How are you?');
        });
    }
}
```

### Using annotations

Instead of configuring the bot manually you can use `@Hears()` annotation. The idea is the same as with 
[`@Route` annotation](https://symfony.com/doc/current/routing.html) from Symfony core. 

```php
use AlexS\BotMan\Annotations\Hears;
use BotMan\BotMan\BotMan;

class GreetingConversation
{
    /**
     * @Hears("Hey!")
     * @Hears("Hi")
     */
    public function hey(BotMan $bot)
    {
        $bot->reply('Hi! How are you?');
    }

    /**
     * @Hears("What is the day {date}")
     */
    public function day(BotMan $bot, $date)
    {
        $day = (new \DateTime($date))->format('l');
        $bot->reply("$date is $day");
    }
}
```

Note that you still need to define you class as a service in Symfony, otherwise the annotations won't be processed.

To be able to use the annotations, please add [`alexeyshockov/botman-annotations` package](https://packagist.org/packages/alexeyshockov/botman-annotations) 
to your Composer deps.

## Middlewares

You can define a BotMan middleware as a service and tag it with `botman.middleware` to automatically add it to the bot.
There are no special requirements, just follow [the general instructions on how to create a middleware](https://botman.io/2.0/middleware).
