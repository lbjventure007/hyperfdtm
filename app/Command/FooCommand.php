<?php

declare(strict_types=1);

namespace App\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Psr\Container\ContainerInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Metric\Contract\MetricFactoryInterface;
#[Command]
class FooCommand extends HyperfCommand
{

    /**
     * @var MetricFactoryInterface
     */
    #[Inject]
    private $metricFactory;

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('demo:command');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Hyperf Demo Command');
    }

    public function handle()
    {
        $counter = $this->metricFactory->makeCounter('demo:command', ['demo:command']);
        $counter->with("demo:command")->add(1);
        // 订单逻辑...
        $this->line('Hello Hyperf!', 'info');
        var_dump("当前时间：".time());
    }
}
