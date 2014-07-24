# NSQ publisher library for PHP

This library ONLY publishes messages to NSQ nodes. Requires standard php socket extension.

## Install

Add to composer.json:

``` json
{
    "require": {
        "data-dog/php-nsq": "~0.2.0"
    }
}
```

## Usage example

``` php
<?php

include __DIR__ . '/vendor/autoload.php';

use Nsq\NsqPool;
use Nsq\Socket\PhpSocket;
use Nsq\Message\JsonMessage;

$nsq = new NsqPool(
    new PhpSocket('127.0.0.1', 4150),
    new PhpSocket('127.0.0.1', 4170)
);

$nsq->publish('my_topic', new JsonMessage(['message' => 'data']));
```

## Run tests

    composer install
    ./bin/phpspec run
