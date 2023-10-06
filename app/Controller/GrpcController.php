<?php
namespace App\Controller;
use Protos\HiClient;
use Protos\HiUser;

class GrpcController extends AbstractController {

    public function hello(){

        $client = new HiClient('localhost:9503',['credentials'=>null,]);
        $request = new HiUser();
        $request->setName('hi test');
        $request->setSex(1);

        list($reply,$status)= $client->sayHello($request);
        $message = $reply->getMessage();
        $user = $reply->getUser();

       // var_dump(memory_get_usage(true));
        return $message.$status;
    }
}