<?php

declare(strict_types=1);

namespace  App\Kafka\Consumer;

use Hyperf\Kafka\AbstractConsumer;
use Hyperf\Kafka\Annotation\Consumer;
use longlang\phpkafka\Consumer\ConsumeMessage;

#[Consumer(topic: "hyperf", nums: 1, groupId: "hyperf", autoCommit: false,enable: true)]
class KafkaConsumer extends AbstractConsumer
{
    public function consume(ConsumeMessage $message): string
    {
        file_put_contents("/tmp/test.log",$message->getTopic().PHP_EOL,8);
        var_dump($message->getTopic() . ':' . $message->getKey() . ':' . $message->getValue());

        //手动确认提交
        $message->getConsumer()->ack($message);

        return "";
    }
}