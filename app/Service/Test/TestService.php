<?php

namespace App\Service\Test;

use App\Components\Test\Object\TestObject;
use App\Components\Test\TestComponent;
use App\Service\BaseService;
use Dleno\CommonCore\Exception\AppException;
use Hyperf\HttpServer\Contract\RequestInterface;

/**
 * HTTP 示例 Service。
 *
 * 展示在 Service 层读取请求头、调用业务组件、本地对象填充和组件方法调用。
 */
class TestService extends BaseService
{
    /**
     * 处理 TestController::test 的示例业务逻辑。
     *
     * @param array $params 已通过 Controller 校验的请求参数
     * @return array
     */
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
