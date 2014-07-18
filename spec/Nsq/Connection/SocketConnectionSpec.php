<?php

namespace spec\Nsq\Connection;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Nsq\Socket\SocketInterface;
use Nsq\Message\MessageInterface;

class SocketConnectionSpec extends ObjectBehavior
{
    function let(SocketInterface $socket, MessageInterface $message)
    {
        $this->beConstructedWith($socket);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Nsq\Connection\SocketConnection');
    }

    function it_should_implement_connection_interface()
    {
        $this->shouldImplement('Nsq\Connection\ConnectionInterface');
    }

    function it_should_publish_message_to_socket($socket, $message)
    {
        $message->payload()->shouldBeCalled()->willReturn('data');
        $cmd = sprintf("PUB topic\n%sdata", pack('N', strlen('data')));
        $socket->write($cmd)->shouldBeCalled();

        $this->publish('topic', $message);
    }
}
