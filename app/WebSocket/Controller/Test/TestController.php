<?php

declare(strict_types=1);

namespace App\WebSocket\Controller\Test;

use App\WebSocket\Controller\BaseController;
use App\WebSocket\Service\Test\TestService;
use Dleno\CommonCore\Websocket\Annotation\WsController;

/**
 * WS 示例 Controller。
 *
 * 展示 WsController 注解、WS 消息参数校验、Service 调用和统一响应。
 */
#[WsController]
class TestController extends BaseController
{
    /**
     * @var TestService
     */
    protected $service;

    /**
     * 示例 WS 指令入口。
     *
     * @return mixed
     */
    public function index()
    {
        //参数校验
        $this->checkParams(
            [
                'userName' => 'required|max:50',
            ]
        );

        $data = $this->service->index($this->request->post());
        return $this->successData($data);
    }
}
