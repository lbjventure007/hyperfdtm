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
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController@index');
Router::addRoute(['GET', 'POST', 'HEAD'], '/es', 'App\Controller\IndexController@es');
Router::addRoute(['GET', 'POST', 'HEAD'], '/suggest', 'App\Controller\IndexController@suggest');
Router::addRoute(['GET', 'POST', 'HEAD'], '/amqp', 'App\Controller\AmqpController@testProducer');
Router::addRoute(['GET', 'POST', 'HEAD'], '/delay', 'App\Controller\AmqpController@delayProducer');
Router::get('/kafka', 'App\Controller\KafkaController@kafka');
Router::get('/favicon.ico', function () {
    return '';
});


//Router::get('/test','App\Controller\GrpcController@hello');
Router::get('/test', 'App\Controller\GrpcController@hello');
//Router::get('/test',function (){
//
//    $grpcController = new  \App\Controller\GrpcController();
//    return  $grpcController->hello();
//});

Router::get('/json','App\Controller\JsonController@testJons');
Router::addServer('grpc',function (){
    Router::addGroup('/grpc.hi',function (){
        Router::post('/sayHello.hi','App\Controller\HiController@sayHello');
    });
});

Router::get('/metrics', function(){
    $registry = Hyperf\Context\ApplicationContext::getContainer()->get(Prometheus\CollectorRegistry::class);
    $renderer = new Prometheus\RenderTextFormat();
    return $renderer->render($registry->getMetricFamilySamples());
});


Router::addGroup('/saga', function () {
    Router::get('/successCase', 'App\Controller\SagaController@successCase');
//    Router::post('/transOut', 'App\Controller\SagaController@transOut', ['middleware' => [\DtmClient\Middleware\DtmMiddleware::class]]);
//    Router::post('/transOutCompensate', 'App\Controller\SagaController@transOutCompensate', ['middleware' => [\DtmClient\Middleware\DtmMiddleware::class]]);
//    Router::post('/transIn', 'App\Controller\SagaController@transIn', ['middleware' => [\DtmClient\Middleware\DtmMiddleware::class]]);
//    Router::post('/transInCompensate', 'App\Controller\SagaController@transInCompensate', ['middleware' => [\DtmClient\Middleware\DtmMiddleware::class]]);
    Router::post('/transOut', 'App\Controller\SagaController@transOut');
    Router::post('/transOutCompensate', 'App\Controller\SagaController@transOutCompensate');
    Router::post('/transIn', 'App\Controller\SagaController@transIn');
    Router::post('/transInCompensate', 'App\Controller\SagaController@transInCompensate');
  });


Router::addGroup('/xa', function () {
    Router::get('/successCase', 'App\Controller\XaController@successCase');
//    Router::post('/transOut', 'App\Controller\SagaController@transOut', ['middleware' => [\DtmClient\Middleware\DtmMiddleware::class]]);
//    Router::post('/transOutCompensate', 'App\Controller\SagaController@transOutCompensate', ['middleware' => [\DtmClient\Middleware\DtmMiddleware::class]]);
//    Router::post('/transIn', 'App\Controller\SagaController@transIn', ['middleware' => [\DtmClient\Middleware\DtmMiddleware::class]]);
//    Router::post('/transInCompensate', 'App\Controller\SagaController@transInCompensate', ['middleware' => [\DtmClient\Middleware\DtmMiddleware::class]]);
    Router::addRoute(['Post','Get'],'/transOut', 'App\Controller\XaController@transOut');

    Router::addRoute(['Post','Get'],'/transIn', 'App\Controller\XaController@transIn');

});