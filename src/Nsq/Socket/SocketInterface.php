<?php

namespace Nsq\Socket;

use Nsq\Message\MessageInterface;

interface SocketInterface
{
    const NSQ_V2 = "  V2";

    const NSQ_RESPONSE = 0;
    const NSQ_ERROR = 1;
    const NSQ_MESSAGE = 2;

    /**
     * Publish a message to NSQ
     *
     * @param string $topic
     * @param \Nsq\MessageInterface $msg
     * @return \Nsq\Response
     */
    function publish($topic, MessageInterface $msg);

    /**
     * Publish multiple messages to NSQ
     *
     * @param string $topic
     * @param array $msgs - elements are instance of \Nsq\Message\MessageInterface
     * @return \Nsq\Response
     */
    function mpublish($topic, array $msgs);
}
