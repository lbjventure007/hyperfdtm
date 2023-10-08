<?php
namespace App\Controller;
use DtmClient\Api\ApiInterface;
use DtmClient\Exception\FailureException;
use DtmClient\Msg;
use DtmClient\TransContext;
use GuzzleHttp\Client;
use GuzzleHttp\ClientTrait;
use GuzzleHttp\Psr7\Request;
use Hyperf\DbConnection\Db;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Guzzle\TestHttp;
use Hyperf\Guzzle\ClientFactory;

use Hyperf\HttpServer\Annotation\GetMapping;
use function Hyperf\CircuitBreaker\Handler\fallback;

#[Controller(prefix: '/msg')]
class MegController extends  AbstractController {
    private Msg $msg;
    protected LoggerInterface $logger;
    protected string $serviceUri = 'http://localhost:9501';
    protected ApiInterface $api;
    public $client;

    public array $options = [];
    public function __construct(ClientFactory $clientFactory,Msg $msg,LoggerFactory $loggerFactory)
    {
        $this->client = $clientFactory->create($this->options);
        $this->msg = $msg;
        $this->logger = $loggerFactory->get('log', 'default');
    }

    #[RequestMapping(path: 'successCase')]
    public function msg():string
    {
        $gid = $this->msg->generateGid();
        TransContext::setGid($gid);
        TransContext::setRetryInterval(1);
       TransContext::setWaitResult(true);
        try {
        //二阶段消息#
        //概述#
        //本文提出的二阶段消息，可以完美替代现有的事务消息或本地消息表架构。无论从复杂度、便利性、性能，还是代码量，
        //新架构都完胜现有架构方案，是这个领域的革命性架构。
        //
        //下面我们以跨行转账作为例子，给大家详解这种新架构。业务场景介绍如下：
        //
        //我们需要跨行从A转给B 30元，我们先进行可能失败的转出操作TransOut，即进行A扣减30元。
        //如果A因余额不足扣减失败，那么转账直接失败，返回错误；如果扣减成功，
        //那么进行下一步转入操作，因为转入操作没有余额不足的问题，可以假定转入操作一定会成功。


        //所以二阶段消息 还是谨慎使用 假如 转入一定成功 这就是一个可能存在的坑
        /* 默认转入是会成功的 */
        $this->msg->add($this->serviceUri.'/msg/transIn', ['name' => 'dtmMsg']);
        \Hyperf\Context\Context::set("isMsgOk",true);
            $this->msg->doAndSubmit($this->serviceUri . '/msg/query?gid=' . $gid, function () {
                /* 转出是可能会成功的 会失败的 如果失败 全局失败  如果成功，因为转入默认都会成功  所以 只有转出能成功就行 */
                $affected = Db::connection("test")->update('UPDATE user set balance = balance-? WHERE id = ? and balance-? >=0 ', [2, 1, 2]); // 返回受影响的行数 int
                if ($affected == 0) {
                    $this->logger->error("msg test fail");
                    \Hyperf\Context\Context::set("isMsgOk",false);
                    throw new FailureException("update user balance fail ");
                }
                $this->logger->error("msg test sucess");

            });
        }catch (\Exception $e){
            var_dump($e->getMessage());
        }
        return $gid.(\Hyperf\Context\Context::get("isMsgOk")==true?" -OK":"-Fail");
    }


    #[RequestMapping(path: 'query')]
    public function query(RequestInterface $request ,ResponseInterface $response):ResponseInterface
    {
        $this->logger->error("msg query sucess");
        /* @var ResponseInterface $respone1 */
        $response1 =  $this->client->get('http://localhost:36789/api/dtmsvr/query?gid='.$request->query('gid',0));

       return $response1;
       //

    }

    #[RequestMapping(path: 'transIn')]
    public function transIn(RequestInterface $request ,ResponseInterface $response):ResponseInterface
    {

        $this->logger->error("msg test1 init");
        $affected = Db::update('UPDATE user set balance = balance+? WHERE id = ? ', [2, 1]); // 返回受影响的行数 int
        if ($affected == 0) {
            $this->logger->error("msg test1 fail");
            return $response->withStatus(409);
        }
        $this->logger->error("msg test1 success");
        return $response->withStatus(200);
    }
}
