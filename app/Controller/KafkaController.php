<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Controller;


use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

use Hyperf\Kafka\Producer;
use Hyperf\Di\Annotation\Inject;
use longlang\phpkafka\Producer\ProduceMessage;
class KafkaController extends AbstractController
{
    protected LoggerInterface $logger;



    public function __construct(LoggerFactory $loggerFactory)
    {
        // 第一个参数对应日志的 name, 第二个参数对应 config/autoload/logger.php 内的 key
        $this->logger = $loggerFactory->get('log', 'default');
    }

    public function Kafka(Producer $producer)
    {
        $error =$res= "";
        $start =time();
        $cost =0;
        try {
//           for ($i=0;$i<10000;$i++) {
//               $res = $producer->sendAsync('hyperf', 'this is conetent', 'this is key');
//           }
//           for ($i=0;$i<10000;$i++) {
//               $res = $producer->send('hyperf', 'this is conetent', 'this is key');
//           }
            $messages=[
                new ProduceMessage('hyperf', 'hyperf1_value', 'hyperf1_key'),
                new ProduceMessage('hyperf', 'hyperf2_value', 'hyperf1_key'),
                new ProduceMessage('hyperf', 'hyperf3_value', 'hyperf1_key'),
            ];
            $res =  $producer->sendBatch($messages);


            $cost =time()-$start;
        }catch (\Throwable $e){
            $error= $e->getMessage();
        }

       return [
           'message'=>$error?$error:"ok",
           'data'=>$res,
           'cost'=>$cost
       ];
    }
}