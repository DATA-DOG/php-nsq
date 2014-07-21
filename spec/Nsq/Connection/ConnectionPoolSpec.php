<?php

namespace spec\Nsq\Connection;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Nsq\Connection\ConnectionPool;
use Nsq\Connection\ConnectionInterface;
use Nsq\Message\MessageInterface;
use Nsq\Response;

class ConnectionPoolSpec extends ObjectBehavior
{
    function let(
        ConnectionInterface $conn1,
        ConnectionInterface $conn2,
        MessageInterface $message
    ) {
        $this->beConstructedWith(ConnectionPool::NSQ_AT_LEAST_ONE);
        $this->addConnection($conn1);
        $this->addConnection($conn2);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Nsq\Connection\ConnectionPool');
    }

    function it_should_implement_connection_interface()
    {
        $this->shouldImplement('Nsq\Connection\ConnectionInterface');
    }

    function it_should_publish_message_to_all_connections($conn1, $conn2, $message)
    {
        $topic = 'topic';
        $conn1->publish($topic, $message)->shouldBeCalled();
        $conn2->publish($topic, $message)->shouldBeCalled();

        $this->publish($topic, $message);
    }

    function it_should_allow_one_node_to_fail($conn1, $conn2, $message)
    {
        $topic = 'topic';
        $conn1->publish($topic, $message)->shouldBeCalled();
        $conn2->publish($topic, $message)->shouldBeCalled()->willThrow(new \Nsq\Exception\PubException('s'));

        $this->publish($topic, $message);
    }

    function it_should_not_allow_both_nodes_to_fail($conn1, $conn2, $message)
    {
        $topic = 'topic';
        $conn1->publish($topic, $message)->shouldBeCalled()->willThrow(new \Nsq\Exception\PubException('s'));
        $conn2->publish($topic, $message)->shouldBeCalled()->willThrow(new \Nsq\Exception\PubException('s'));

        $this->shouldThrow('Nsq\Exception\PubException')->duringPublish($topic, $message);
    }
}
