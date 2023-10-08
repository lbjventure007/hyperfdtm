<?php

namespace App\Controller;


use Exception;
use Co\Context;
use DtmClient\DbTransaction\DBTransactionInterface;
use DtmClient\DbTransaction\HyperfDbTransaction;
use DtmClient\TransContext;
use DtmClient\XA;
use Hyperf\Contract\ConfigInterface;
use Hyperf\DbConnection\Aspect\TransactionAspect;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Http\Message\ResponseInterface;
use Hyperf\Config\Config;
use Psr\Log\LoggerInterface;
use function Hyperf\CircuitBreaker\Handler\fallback;
use function Symfony\Component\Translation\t;
use \Hyperf\DbConnection\Db;

class XaController extends AbstractController
{

    protected $xaBool = true;
    // private GrpcClient $grpcClient;


    protected string $serviceUri = 'http://localhost:9501';
    #[Inject]
    private Xa $xa;


    #[Inject]
    private ConfigInterface $config;


    protected LoggerInterface $logger;


    public function __construct(ConfigInterface $config, LoggerFactory $loggerFactory)
    {
        $server = $this->config->get('dtm.server', 'localhost');
        $port = $this->config->get('dtm.port.http', 36789);
        $hostname = $server . ':' . $port;
        $this->logger = $loggerFactory->get('log', 'default');
        // $this->grpcClient = new GrpcClient($hostname);
    }


    public function successCase(): string
    {
        $payload = ['amount' => 10];
        // 开启Xa 全局事物
        $gid = $this->xa->generateGid();
        var_dump(11);
        try {
            TransContext::setWaitResult(true);
            TransContext::setRetryInterval(1);

            $this->xa->globalTransaction($gid, function () use ($payload) {

                // 调用子事物接口
                $respone = $this->xa->callBranch($this->serviceUri . '/xa/transIn', $payload);
                // XA http模式下获取子事物返回结构
                /* @var ResponseInterface $respone */
                $respone->getBody()->getContents();
                // 调用子事物接口
                $payload = ['amount' => 10];
                /* @var ResponseInterface $response1 */
                $response1 = $this->xa->callBranch($this->serviceUri . '/xa/transOut', $payload);
                $response1->getBody()->getContents();

            });
        } catch (\Throwable $e) {
            var_dump($e->getMessage());
        }
        // 通过 TransContext::getGid() 获得 全局事务ID 并返回
        return TransContext::getGid();
    }

    public function transIn(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $content = $request->post('amount');
        $amount = $content['amount'] ?? 10;
        // 模拟分布式系统下transIn方法
        \Hyperf\Context\Context::set("boo", true);


        $this->xa->localTransaction(function (DBTransactionInterface $dbTransaction) use ($amount) {
            // 请使用 DBTransactionInterface 处理本地 Mysql 事物
            //   $config = Config::get("databases.test");
            // var_dump('11111',$config);


            $int = $dbTransaction->xaExecute('UPDATE `user` set `balance` = `balance` + ? where id = 1', [$amount]);
            if ($int == 0) {
                \Hyperf\Context\Context::set("boo", false);
            }

        });
        if (\Hyperf\Context\Context::get("boo") == false) {
            $this->logger->error(" xa tranin fail gid: is " . $request->query("gid", 0)); //记录gid 以便查找原因等
            return $response->withStatus(409);
        }
        return $response->withStatus(200);

    }


    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return array
     * @throws \Exception
     */
    public function transOut(RequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {

            $content = $request->post('amount');
            $amount = $content['amount'] ?? 10;
            // 模拟分布式系统下transOut方法

            \Hyperf\Context\Context::set("boo1", true); //这里只能

            $this->xa->localTransaction(function (DBTransactionInterface $dbTransaction) use ($amount) {


                // 请使用 DBTransactionInterface 处理本地 Mysql 事物
                $int = $dbTransaction->xaExecute('UPDATE `user` set `balance` = `balance` - ? where id = 2 and (`balance`-?) >= 0', [$amount, $amount]);
                if ($int == 0) {
                    \Hyperf\Context\Context::set("boo1", false);
                }
            });

            if (\Hyperf\Context\Context::get("boo1") == true) {
                return $response->withStatus(200);
            }
            $this->logger->error('xa transout :', ['message' => "修改失败"]);
//        return $response->withStatus(409);
            throw new \Exception("hahah");


        } catch (\Throwable $e) {

            throw new \Exception("error");
        }

    }

}
