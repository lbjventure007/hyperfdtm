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
use Hyperf\Elasticsearch\ClientBuilderFactory;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\RateLimit\Annotation\RateLimit;
use Psr\Log\LoggerInterface;

class IndexController extends AbstractController
{
    protected LoggerInterface $logger;

    public function __construct(LoggerFactory $loggerFactory)
    {
        // 第一个参数对应日志的 name, 第二个参数对应 config/autoload/logger.php 内的 key
        $this->logger = $loggerFactory->get('log', 'default');
    }
    #[RateLimit(create:1,capacity:1,limitCallback:[IndexController::class,"limitCallback"])]
    public function index()
    {
        try {
            $this->logger->info("testtt",["testt"=>"a"]);
            $user = $this->request->input('user', 'Hyperf');
            $method = $this->request->getMethod();

            return [
                'method' => $method,
                'message' => "Hello 1 {$user}.",
            ];
        }catch (\Exception $exception){
            return [
                "message"=>$exception->getMessage()
            ];
        }
    }

    public static function limitCallback(float $seconds, ProceedingJoinPoint $proceedingJoinPoint)
    {
//        var_dump("limit");
        return ["message"=>"limit","code"=>400];
        // $seconds 下次生成Token 的间隔, 单位为秒
        // $proceedingJoinPoint 此次请求执行的切入点
        // 可以通过调用 `$proceedingJoinPoint->process()` 继续完成执行，或者自行处理
        return $proceedingJoinPoint->process();
    }


    public function es(RequestInterface $request,ResponseInterface $response):ResponseInterface
    {

        $builder = $this->container->get(ClientBuilderFactory::class)->create();

        $client = $builder->setHosts(['http://127.0.0.1:9200'])->build();

        $json = '{
    "query" : {
        "match_all" : { }
        }
}';


        
        $res = $client->search(
            [
                "index"=>'test5',
                'body'=>$json,
            ]
        );
        $data = [];
        if (isset($res['hits']['hits']) && count($res['hits']['hits']) > 0) {
            $data1 = $res['hits']['hits'];
            foreach ($data1  as $d ){
                $data[]= $d['_source'];
            }
        }

        return $response->json($data);
    }


    public function suggest(RequestInterface $request,ResponseInterface $response):ResponseInterface
    {

        $builder = $this->container->get(ClientBuilderFactory::class)->create();

        $client = $builder->setHosts(['http://127.0.0.1:9200'])->build();

        $json = '{
  "suggest": {
    "info_suggest": {
      "text": "'.$request->query("text","l").'",
      "completion":{
        "field":"info",
        "skip_duplicates":true,
        "size": 10
  
      }
    }
  }
}';

        $res = $client->search(
            [
                "index"=>'test5',
                'body'=>$json,
            ]
        ) ;

        $data = [];

        if (isset($res['suggest']['info_suggest'][0]['options']) && count($res['suggest']['info_suggest'][0]['options']) > 0) {
            $data= $res['suggest']['info_suggest'][0]['options'];
        }

        return $response->json($data);
    }
}
