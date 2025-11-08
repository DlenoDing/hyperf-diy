<?php

namespace App\Service\Test;

use App\Components\Test\Object\TestObject;
use App\Components\Test\TestComponent;
use App\Service\BaseService;
use Dleno\CommonCore\Exception\AppException;
use Hyperf\HttpServer\Contract\RequestInterface;


class TestService extends BaseService
{
    public function test(array $params)
    {
        //钉钉告警
        //ding_talk()->notice('ssss');
        //ding_talk('trace')->exception(new AppException('ddd'));
        //ding_talk('其他机器人配置key')->notice('222222');

        $headers = get_inject_obj(RequestInterface::class)->getHeaders();

        $testObj = get_inject_obj(TestComponent::class)->getData($params['key'] ?? 'test');
        if ($testObj->getId()) {
            get_inject_obj(TestComponent::class)->test1($testObj);
        } else {
            $data    = [
                'key'   => 'test',
                'attr1' => '1',
                'attr2' => 'attr2',
                'attr3' => 'attr3',
            ];
            $testObj = (new TestObject())->fill($data);
            get_inject_obj(TestComponent::class)->test2($testObj);
        }

        return [
            'header' => $headers,
        ];
    }
}