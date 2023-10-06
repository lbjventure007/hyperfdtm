<?php
namespace App\Controller;
use Hyperf\Context\ApplicationContext;
use App\JsonRpc\CalculatorServiceInterface;
use Hyperf\CircuitBreaker\Annotation\CircuitBreaker;



class JsonController extends AbstractController {
    #[CircuitBreaker(options: ['timeout' => 0.005],duration:3, failCounter: 1, successCounter: 1, fallback: "App\Service\UserService::searchFallback")]
    public function testJons(){
        $client = ApplicationContext::getContainer()->get(CalculatorServiceInterface::class);


        $result = $client->calculate(1, 1);
        return $result;
    }
}
