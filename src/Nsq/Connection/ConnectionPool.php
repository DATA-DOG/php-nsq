<?php

namespace Nsq\Connection;

use Nsq\Message\MessageInterface;
use Nsq\Exception\PubException;

class ConnectionPool implements ConnectionInterface
{
    const NSQ_QUORUM = 0;
    const NSQ_AT_LEAST_ONE = 1;
    const NSQ_AT_LEAST_TWO = 2;

    private $connections = array();
    private $consistency = self::NSQ_AT_LEAST_ONE;

    public function __construct($consistency = self::NSQ_AT_LEAST_ONE)
    {
        $this->consistency = $consistency;
    }

    public function addConnection(ConnectionInterface $connection)
    {
        $this->connections[] = $connection;
    }

    /**
     * {@inheritDoc}
     */
    public function publish($topic, MessageInterface $msg)
    {
        $required = $this->consistency;
        if ($this->consistency = self::NSQ_QUORUM) {
            $required = ceil(count($this->connections) / 2) + 1;
        }
        $success = 0;
        foreach ($this->connections as $connection) {
            try {
                $connection->publish($topic, $msg);
                $success++;
            } catch(PubException $e) {}
        }
        if ($required > $success) {
            throw new PubException("Required at leat {$required} nodes to be successful, but only {$success} were.");
        }
    }

    /**
     * {@inheritDoc}
     */
    public function mpublish($topic, array $msgs)
    {
        $required = $this->consistency;
        if ($this->consistency = self::NSQ_QUORUM) {
            $required = ceil(count($this->connections) / 2) + 1;
        }
        $success = 0;
        foreach ($this->connections as $connection) {
            try {
                $connection->mpublish($topic, $msgs);
                $success++;
            } catch(PubException $e) {}
        }
        if ($required > $success) {
            throw new PubException("Required at leat {$required} nodes to be successful, but only {$success} were.");
        }
    }
}
