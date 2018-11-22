<?php
/**
 * Created by human.
 * User: Weinan Tang <twn39@163.com>
 * Date: 2018/11/22
 * Time: 下午1:26
 */
namespace RabbitMQ\Message;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class DelayMessage implements DelayMessageInterface
{
    private $exchange;

    private $queue;

    private $channel;

    private $connection;

    private $bound = false;

    public function __construct(AMQPStreamConnection $connection)
    {
        $this->connection = $connection;
        $this->channel = $connection->channel();
    }

    public function bind()
    {
        if (empty($this->exchange)) {
            throw new MQException('RabbitMQ delay exchange is required !');
        }
        if (empty($this->queue)) {
            throw new MQException('RabbitMQ delay queue is required !');
        }
        $this->channel->exchange_declare($this->exchange, 'x-delayed-message', false, true, false, false, false, new AMQPTable(array(
            'x-delayed-type' => 'fanout',
        )));
        $this->channel->queue_declare($this->queue, false, false, false, false, false, new AMQPTable(array(
            'x-dead-letter-exchange' => 'delayed',
        )));
        $this->channel->queue_bind($this->queue, $this->exchange);
        $this->bound = true;

        // 连接是在外部引入，因此关闭连接不应在对象回收时，而是在整个php生命周期结束时
        register_shutdown_function(function ($channel, $connection) {
            $channel->close();
            $connection->close();
        }, $this->channel, $this->connection);
    }

    public function setExchange($exchange)
    {
        $this->exchange = $exchange;
    }

    public function setQueue($queue)
    {
        $this->queue = $queue;
    }

    public function publish($message, $ttl)
    {
        if (!$this->bound) {
            $this->bind();
        }
        $headers = new AMQPTable(array('x-delay' => $ttl));
        $message = new AMQPMessage(json_encode($message), array('delivery_mode' => 2));
        $message->set('application_headers', $headers);
        $this->channel->basic_publish($message, $this->exchange);
        return true;
    }

    /**
     * @param \Closure $callback
     * @throws MQException
     * @throws \ErrorException
     */
    public function consume(\Closure $callback)
    {
        if (!$this->bound) {
            $this->bind();
        }
        $this->channel->basic_consume($this->queue, '', false, true, false, false, $callback);
        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }

    /**
     * @return mixed
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * @return mixed
     */
    public function getExchange()
    {
        return $this->exchange;
    }
}