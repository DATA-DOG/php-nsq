<?php

namespace spec\Nsq\Connection;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Nsq\Socket\SocketInterface;
use Nsq\Message\MessageInterface;
use Nsq\Response;

class SocketConnectionSpec extends ObjectBehavior
{
    function let(SocketInterface $socket, MessageInterface $message, Response $response)
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

    function it_should_publish_message_to_socket($socket, $message, $response)
    {
        $response->isOk()->shouldBeCalled()->willReturn(true);
        $socket->publish('topic', $message)->shouldBeCalled()->willReturn($response);

        $this->publish('topic', $message);
    }

    function it_should_throw_exception_if_publish_message_fails($socket, $message, $response)
    {
        $response->isOk()->shouldBeCalled()->willReturn(false);
        $response->code()->shouldBeCalled()->willReturn("E_FAILED");
        $socket->publish('topic', $message)->shouldBeCalled()->willReturn($response);

        $this->shouldThrow('Nsq\Exception\PubException')->duringPublish('topic', $message);
    }
}
