<?php

namespace App\Service\Test;

use App\Components\Test\Object\TestObject;
use App\Components\Test\TestComponent;
use App\Service\BaseService;
use Hyperf\HttpServer\Contract\RequestInterface;


class TestService extends BaseService
{
    public function test(array $params)
    {
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