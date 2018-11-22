<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use RabbitMQ\Message\DelayMessage;

require __DIR__.'/../vendor/autoload.php';

$connection = new AMQPStreamConnection('localhost', 5672, 'twn39', 'tangweinan', '/');
$delayMessage = new DelayMessage($connection);
$delayMessage->setExchange('delay-exchange');
$delayMessage->setQueue('delay-queue');
$delayMessage->consume(function (AMQPMessage $message) {
    var_dump(json_decode($message->body, true));
});
