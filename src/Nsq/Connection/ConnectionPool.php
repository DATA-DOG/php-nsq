<?php

namespace Nsq\Connection;

use Nsq\Message\MessageInterface;

class ConnectionPool implements ConnectionInterface
{
    private $connections = array();

    public function addConnection(ConnectionInterface $connection)
    {
        $this->connections[] = $connection;
    }

    /**
     * {@inheritDoc}
     */
    public function publish($topic, MessageInterface $msg)
    {
        foreach ($this->connections as $connection) {
            $connection->publish($topic, $msg);
        };
    }
}
