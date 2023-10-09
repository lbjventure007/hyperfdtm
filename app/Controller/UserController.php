<?php
namespace App\Controller;


use App\Model\User;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;

#[AutoController]
class UserController extends AbstractController {


    public function index(RequestInterface $request, ResponseInterface $response):ResponseInterface
    {
        $user = new User();
        $user->name = "test001";
        $user->gender = 1;
        $user->balance=1;
        $user->save();

        return $response->json(["code"=>200]);

    }

    public function search(RequestInterface $request, ResponseInterface $response):ResponseInterface
    {
       // $user = User::search("test001")->get();

        /**
         * Hyperf\Scout\Engine\ElasticsearchEngine
         * 225行
         *    'must' => [['query_string' => ['query' => "*{$builder->query}*"]]], 这里的*好 最好去掉  交给用户去做匹配
         *    因为query_string 有其他可做匹配的  如果 *balance:4.00 OR name:test001*
         *    这样的要求查询的话 会报错  最好交给用户做匹配好一点 下面是query_string 一些简单使用
         *    https://blog.csdn.net/tergou/article/details/131507714
         *   以下操作都可以
         */
        $res = User::search("(balance:2 AND name:test0*) OR (id:(>=11100  AND <11112))")->get();
        //$res = User::search("(balance:2 AND name:test0*) OR (id:(>=11100 ))")->get();
        //$res = User::search("(balance:2 AND name:test0*) OR id:11112")->get();
        //$res = User::search("balance:2 AND name:test0* AND id:11112")->get();
        //$res = User::search("balance:4.00 OR name:test0*")->get();
        //$res = User::search("balance:4.00 OR name:test001")->get();
       // $res = User::search("test001")->get();
       // $res = User::search("test*")->get();
       // $res = User::search("4.0*")->get();
       // $res = User::search("*001")->get();

        return $response->json(["code"=>200,'data'=>$res]);

    }


    public function update(RequestInterface $request, ResponseInterface $response):ResponseInterface
    {
        $id = $request->query("id",0);
        $balance = $request->query("balance",0);

        $user = User::where("id","=",$id)->first();
        if (empty($user)) {
            return $response->json(["code"=>400,'message'=>"请求参数不存在"]);
        }
        $user->balance= $balance;

        return $response->json(["code"=>200,'message'=> $user->save()?"更新成功":"更新失败"]);
    }
}
