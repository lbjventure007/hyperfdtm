<?php
namespace App\Controller;

use DtmClient\TCC;
use DtmClient\TransContext;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Throwable;

#[Controller(prefix: '/tcc')]
class TccController
{

    protected string $serviceUri = 'http://127.0.0.1:9501';

    #[Inject]
    protected TCC $tcc;

    #[GetMapping(path: 'successCase')]
    public function successCase()
    {
        try {

            $this->tcc->globalTransaction(function (TCC $tcc) {
                // 创建子事务 A 的调用数据
                $tcc->callBranch(
                // 调用 Try 方法的参数
                    ['amount' => 30],
                    // Try 方法的 URL
                    $this->serviceUri . '/tcc/transA/try',
                    // Confirm 方法的 URL
                    $this->serviceUri . '/tcc/transA/confirm',
                    // Cancel 方法的 URL
                    $this->serviceUri . '/tcc/transA/cancel'
                );
                // 创建子事务 B 的调用数据，以此类推
                $tcc->callBranch(
                    ['amount' => 30],
                    $this->serviceUri . '/tcc/transB/try',
                    $this->serviceUri . '/tcc/transB/confirm',
                    $this->serviceUri . '/tcc/transB/cancel'
                );
            });
        } catch (Throwable $e) {
            var_dump($e->getMessage(), $e->getTraceAsString());
        }
        // 通过 TransContext::getGid() 获得 全局事务ID 并返回
        return TransContext::getGid();
    }
}
