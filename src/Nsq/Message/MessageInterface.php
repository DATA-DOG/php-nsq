<?php

namespace Nsq\Message;

interface MessageInterface
{
    /**
     * Transform message to string
     *
     * @return string
     */
    function payload();
}
