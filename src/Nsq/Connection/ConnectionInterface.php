<?php

namespace Nsq\Connection;

use Nsq\Message\MessageInterface;

interface ConnectionInterface
{
    /**
     * Publish a message to NSQ
     *
     * @param string $topic
     * @param \Nsq\MessageInterface $msg
     * @return void
     */
    function publish($topic, MessageInterface $msg);
}
