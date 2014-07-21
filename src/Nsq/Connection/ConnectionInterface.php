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

    /**
     * Publish multiple messages to NSQ
     *
     * @param string $topic
     * @param array $msgs - elements are instance of \Nsq\Message\MessageInterface
     * @return void
     */
    function mpublish($topic, array $msgs);
}
