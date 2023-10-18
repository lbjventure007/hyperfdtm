<?php

declare(strict_types=1);

namespace App\Amqp\Consumer;

use Hyperf\Amqp\Result;
use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\Message\ConsumerMessage;
use Hyperf\Logger\LoggerFactory;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

#[Consumer(exchange: 'hyperf', routingKey: 'hyperf', queue: 'hyperf', name: "DemoConsumer", nums: 1)]
class DemoConsumer extends ConsumerMessage
{


    protected LoggerInterface $logger;

    public function __construct(LoggerFactory $loggerFactory)
    {
        // 第一个参数对应日志的 name, 第二个参数对应 config/autoload/logger.php 内的 key
        $this->logger = $loggerFactory->get('log', 'default');
    }

    public function consumeMessage($data, AMQPMessage $message): string
    {
       // file_put_contents("/tmp/test.log",var_export($data,true).PHP_EOL,8);
        $this->logger->info("demoConsumer",['data'=>$data]);
        print_r($data);
        return Result::ACK;
    }

    public function isEnable(): bool
    {
        return false; // TODO: Change the autogenerated stub
    }
}
