# NSQ publisher library for PHP

This library ONLY publishes messages to NSQ nodes. Requires standard php socket extension.

## Usage

``` php
<?php

include __DIR__ . '/vendor/autoload.php';

$nsq = new Nsq\Connection\SocketConnection(
    new Nsq\Socket\PhpSocket('127.0.0.1', 4150)
);
$nsq->publish('my_topic', new Nsq\Message\JsonMessage(['message' => 'data']));
```
