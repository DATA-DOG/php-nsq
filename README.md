# NSQ publisher library for PHP

This library ONLY publishes messages to NSQ nodes. Requires standard php socket extension.

## Install

Add to composer.json:

``` json
{
    "require": {
        "data-dog/php-nsq": "~0.1.0"
    }
}
```

## Usage

Single connection:

``` php
<?php

include __DIR__ . '/vendor/autoload.php';

use Nsq\Connection\SocketConnection;
use Nsq\Socket\PhpSocket;
use Nsq\Message\JsonMessage;

$nsq = new SocketConnection(
    new PhpSocket('127.0.0.1', 4150)
);
$nsq->publish('my_topic', new JsonMessage(['message' => 'data']));
```

Connection pool:

``` php
<?php

include __DIR__ . '/vendor/autoload.php';

use Nsq\Connection\ConnectionPool;
use Nsq\Connection\SocketConnection;
use Nsq\Socket\PhpSocket;
use Nsq\Message\JsonMessage;

$nsq = new ConnectionPool();
$nsq->addConnection(SocketConnection(new PhpSocket('127.0.0.1', 4150));
$nsq->addConnection(SocketConnection(new PhpSocket('127.0.0.1', 4170));

$nsq->publish('my_topic', new JsonMessage(['message' => 'data']));
```

