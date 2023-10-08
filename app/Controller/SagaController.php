<?php

namespace App\Controller;

use DtmClient\Saga;
use DtmClient\TransContext;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use function Hyperf\Framework\Logger\info;
use DtmClient\Annotation\Barrier;
use Psr\Log\LoggerInterface;
use Hyperf\Logger\LoggerFactory;
use DtmClient\Middleware\DtmMiddleware;




class SagaController extends AbstractController
{
    protected LoggerInterface $logger;
    protected string $serviceUri = 'http://127.0.0.1:9501';
    public function __construct(LoggerFactory $loggerFactory)
    {
        // 第一个参数对应日志的 name, 第二个参数对应 config/autoload/logger.php 内的 key
        $this->logger = $loggerFactory->get('log', 'default');
    }


    #[Inject]
    protected Saga $saga;


    public function successCase(): string
    {
        try {
            $payload = ['amount' => 50];

           // TransContext::setRetryInterval(1);

            // 初始化 Saga 事务
            $this->saga->init();
            // 增加转出子事务
            $this->saga->add(
                $this->serviceUri . '/saga/transOut',
                $this->serviceUri . '/saga/transOutCompensate',
                $payload
            );
            // 增加转入子事务
            $this->saga->add(
                $this->serviceUri . '/saga/transIn',
                $this->serviceUri . '/saga/transInCompensate',
                $payload
            );


            // 提交 Saga 事务
            $this->saga->submit();
        }catch (\Throwable $e){
            var_dump($e->getMessage());
        }
        // 通过 TransContext::getGid() 获得 全局事务ID 并返回
        return TransContext::getGid();
    }


    #[Barrier]
    public function transOut(RequestInterface $request,ResponseInterface $response):ResponseInterface
    {

        $this->logger->error("transOut in",["affecatd"=>"aa"]);

       try{
            $affected = Db::connection("test")->update('UPDATE user set balance = balance-? WHERE id = ? and balance-? >=0 ', [2, 1,2]); // 返回受影响的行数 int

            $this->logger->error("transOut affect:",["affecatd"=>$affected]);
            if ($affected==1) {
                $this->logger->error("transOut ok");

               return $response->withStatus(200);

            }
            $this->logger->error("transOut fail gid is ".$request->query("gid",0));

       }catch (\Throwable $e) {
           $this->logger->error("transOut fail",['fail'=>$e->getMessage()]);

       }
        /*   下面两个执行的效果一样的 */
        //return $response->withStatus(409); //
        throw new \Exception("error");
    }

    #[Barrier]
    public function transOutCompensate(RequestInterface $request,ResponseInterface $response):ResponseInterface
    {


        try {


            $this->logger->error("transOutCompensate start:",["in"=>"sss"]);

            $affected = Db::connection("test")->update('UPDATE user set balance = balance+? WHERE id = ?', [2, 1]); // 返回受影响的行数 int

            $this->logger->error("transOutCompensate affect:" ,["message"=>$affected]);
            if ($affected == 1) {
              return  $response->withStatus(200);
            }


        }catch (\Throwable $e) {
            $this->logger->error("transOutCompensate ex",['mss'=>'a1']);
        }
        throw new \Exception("error");
        //return  $response->withStatus(409);

    }


    public function transIn(RequestInterface $request,ResponseInterface $response):ResponseInterface
    {
       try {
           $affected = Db::update('UPDATE user set balance = balance+? WHERE id = ?', [2, 2]); // 返回受影响的行数 int
           $this->logger->info("transIn affect:" . $affected);
           if ($affected == 1) {
               return  $response->withStatus(200);
           }
           $this->logger->info("transIn fail",['message'=>"409"]);
           throw new \Exception("ee");

       }catch (\Throwable $e) {
           $this->logger->info("transIn fail",['message'=>$e->getMessage()]);
           throw new \Exception("ee");
       }

    }


    #[Barrier]
    public function transInCompensate(RequestInterface $request,ResponseInterface $response):array
    {
        try {
            $this->logger->info("transInCompensate start:");
            $affected = Db::update('UPDATE user set balance = balance-? WHERE id = ?', [2, 2]); // 返回受影响的行数 int
            $this->logger->info("transInCompensate affect:".$affected,["in"=>$affected]);
            if ($affected==1) {
                return     $response->withStatus(200);
            }
            throw new \Exception("err");
        } catch (\Throwable $exception) {
            $this->logger->info("transInCompensate fail",["fail"=>$exception->getMessage()]);
            throw new \Exception("err");
        }

    }
}
