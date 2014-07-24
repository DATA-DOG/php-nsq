<?php

namespace spec\Nsq;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Nsq\NsqPool;
use Nsq\Socket\SocketInterface;
use Nsq\Message\MessageInterface;
use Nsq\Response;

class NsqPoolSpec extends ObjectBehavior
{
    function let(
        SocketInterface $conn1,
        SocketInterface $conn2,
        MessageInterface $message,
        Response $failed,
        Response $success
    ) {
        $this->beConstructedWith($conn1, $conn2);

        $failed->isOk()->willReturn(false);
        $failed->code()->willReturn('E_FAILED');

        $success->isOk()->willReturn(true);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Nsq\NsqPool');
    }

    function it_should_publish_message_to_all_connections($conn1, $conn2, $message, $success)
    {
        $topic = 'topic';
        $conn1->publish($topic, $message)->shouldBeCalled()->willReturn($success);
        $conn2->publish($topic, $message)->shouldBeCalled()->willReturn($success);

        $this->publish($topic, $message, NsqPool::NSQ_ALL);
    }

    function it_should_fail_if_not_all_messages_are_published($conn1, $conn2, $message, $success, $failed)
    {
        $topic = 'topic';
        $conn1->publish($topic, $message)->shouldBeCalled()->willReturn($success);
        $conn2->publish($topic, $message)->shouldBeCalled()->willReturn($failed);

        $this->shouldThrow('Nsq\Exception\PubException')->duringPublish($topic, $message, NsqPool::NSQ_ALL);
    }

    function it_should_allow_one_node_to_fail_by_default($conn1, $conn2, $message, $success, $failed)
    {
        $topic = 'topic';
        $conn1->publish($topic, $message)->shouldBeCalled()->willReturn($success);
        $conn2->publish($topic, $message)->shouldBeCalled()->willReturn($failed);

        $this->publish($topic, $message);
    }

    function it_should_not_allow_both_nodes_to_fail_by_default($conn1, $conn2, $message, $failed)
    {
        $topic = 'topic';
        $conn1->publish($topic, $message)->shouldBeCalled()->willReturn($failed);
        $conn2->publish($topic, $message)->shouldBeCalled()->willReturn($failed);

        $this->shouldThrow('Nsq\Exception\PubException')->duringPublish($topic, $message);
    }

    function it_should_publish_to_only_one_node($conn1, $conn2, $message, $success)
    {
        $topic = 'topic';
        $conn1->publish($topic, $message)->shouldBeCalled()->willReturn($success);
        $conn2->publish($topic, $message)->shouldNotBeCalled();

        $this->publish($topic, $message, NsqPool::NSQ_ONLY_ONE);
    }

    function it_should_fail_if_none_of_nodes_were_successful($conn1, $conn2, $message, $failed)
    {
        $topic = 'topic';
        $conn1->publish($topic, $message)->shouldBeCalled()->willReturn($failed);
        $conn2->publish($topic, $message)->shouldBeCalled()->willReturn($failed);

        $this->shouldThrow('Nsq\Exception\PubException')->duringPublish($topic, $message, NsqPool::NSQ_ONLY_ONE);
    }
}
