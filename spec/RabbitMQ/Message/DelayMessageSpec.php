<?php

namespace spec\RabbitMQ\Message;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use RabbitMQ\Message\DelayMessage;
use PhpSpec\ObjectBehavior;
use RabbitMQ\Message\DelayMessageInterface;

class DelayMessageSpec extends ObjectBehavior
{
    function let()
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'twn39', 'tangweinan', '/');
        $this->beConstructedWith($connection);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DelayMessage::class);
        $this->shouldImplement(DelayMessageInterface::class);
    }

    function it_can_set_exchange()
    {
        $this->setExchange('delay-exchange')->shouldBeNull();
    }

    function it_can_set_queue()
    {
        $this->setQueue('delay-queue')->shouldBeNull();
    }

    function it_publish_message()
    {
        $this->setExchange('delay-exchange');
        $this->setQueue('delay-queue');
        $this->publish([
            'message' => 'hello',
        ], 5000);
    }

}
