<?php

declare(strict_types=1);

namespace App\Amqp\Producer;

use Hyperf\Amqp\Annotation\Producer;
use Hyperf\Amqp\Message\ProducerDelayedMessageTrait;
use Hyperf\Amqp\Message\ProducerMessage;
use Hyperf\Amqp\Message\Type;

#[Producer]
class DelayDirectProducer extends ProducerMessage
{
    use ProducerDelayedMessageTrait;

    protected string $type = Type::DIRECT;

    protected string$exchange = 'ex.hyperf.delay';

    protected array|string $routingKey = '';




    public function __construct($data)
    {
        $this->payload = $data;
    }
}
