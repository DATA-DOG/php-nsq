<?php

namespace Nsq\Connection;

use Nsq\Message\MessageInterface;
use Nsq\Socket\SocketInterface;
use Nsq\Exception\PubException;

class SocketConnection implements ConnectionInterface
{
    private $socket;

    public function __construct(SocketInterface $socket)
    {
        $this->socket = $socket;
    }

    /**
     * {@inheritDoc}
     */
    public function publish($topic, MessageInterface $msg)
    {
        $response = $this->socket->publish($topic, $msg);
        if (!$response->isOk()) {
            throw new PubException("PUB failed to '{$topic}', response: {$response->code()}");
        }
    }

    /**
     * {@inheritDoc}
     */
    public function mpublish($topic, array $msgs)
    {
        $response = $this->socket->mpublish($topic, $msgs);
        if (!$response->isOk()) {
            throw new PubException("PUB failed to '{$topic}', response: {$response->code()}");
        }
    }
}
