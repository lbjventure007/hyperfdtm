<?php
namespace Protos;
class HiClient extends \Hyperf\GrpcClient\BaseClient {
    public function sayHello(HiUser $argument){
        return $this->_simpleRequest(
          '/grpc.hi/sayHello.hi',
            $argument,
            [ HiReply::class,'decode']
        );
    }
}
