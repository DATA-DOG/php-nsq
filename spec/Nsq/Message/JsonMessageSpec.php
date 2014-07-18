<?php

namespace spec\Nsq\Message;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class JsonMessageSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Nsq\Message\JsonMessage');
    }

    function it_should_implement_message_interface()
    {
        $this->shouldImplement('Nsq\Message\MessageInterface');
    }

    function it_should_transform_payload_to_json()
    {
        $this->beConstructedWith(array('key' => 'val', 'arr' => []));
        $this->payload()->shouldBe('{"key":"val","arr":[]}');
    }
}
