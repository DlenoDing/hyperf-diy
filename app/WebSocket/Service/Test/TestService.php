<?php

namespace App\WebSocket\Service\Test;

use App\WebSocket\Service\BaseService;


class TestService extends BaseService
{
    public function index($post)
    {
        /*$wssCpt = get_inject_obj(WebSocketServerComponent::class);

        $cursor = 1;
        for ($i = 0; $i <= 3; $i++) {
            $ret = $wssCpt->getClients($cursor, 5);
            var_dump($cursor);
            var_dump($ret);
        }*/
        //AsyncQueue::push(new TestJob(mt_rand(100,999)));
        //var_dump(get_inject_obj(UserComponent::class)->getUsersByNum(3));
        //get_header_val('Client-Token')
        return ['r' => 'a'];
    }
}
