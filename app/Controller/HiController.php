<?php
namespace App\Controller;

class HiController extends AbstractController {
    public function sayHello(\Protos\HiUser $user){
        $message = new \Protos\HiReply();
        $message->setMessage($user->getName());
        $message->setUser($user);
        return $message;
    }
}