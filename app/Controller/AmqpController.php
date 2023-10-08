<?php
namespace App\Controller;

use App\Amqp\Producer\DelayDirectProducer;
use App\Amqp\Producer\DemoProducer;
use Hyperf\Amqp\Producer;

use Hyperf\Context\ApplicationContext;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Hyperf\Di\Annotation\Inject;
use function Hyperf\JsonRpc\response;

class AmqpController extends AbstractController {

    #[Inject]
    private  Producer $producer;


    public function testProducer(RequestInterface $request,ResponseInterface $response):ResponseInterface
    {
        $message = new DemoProducer(["test"=>"message"]);
        $this->producer->produce($message);
       return $response->json(["code"=>200]);
    }

    public function delayProducer(RequestInterface $request,ResponseInterface $response):ResponseInterface
    {
        $message = new DelayDirectProducer(["test"=>"message","time"=>date('Y-m-d H:i:s')]);
        $message->setDelayMs(10000);


        return $response->json(["code"=>200,"isSendOk"=> $this->producer->produce($message)]);
    }
}

