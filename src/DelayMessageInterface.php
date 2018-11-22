<?php
/**
 * Created by human.
 * User: Weinan Tang <twn39@163.com>
 * Date: 2018/11/22
 * Time: 下午1:26
 */
namespace RabbitMQ\Message;

interface DelayMessageInterface
{

    public function publish($message, $ttl);

    public function consume(\Closure $callback);
}