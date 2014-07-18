<?php

namespace Nsq\Connection;

use Nsq\Message\MessageInterface;
use Nsq\Socket\SocketInterface;

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
        $msg = $msg->payload();
        $cmd = sprintf("PUB %s\n%s", $topic, pack('N', strlen($msg)) . $msg);
        $this->socket->write($cmd);
    }
}
