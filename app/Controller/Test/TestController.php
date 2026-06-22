<?php

namespace App\Controller\Test;

use App\Controller\BaseController;
use App\Service\Test\TestService;
use Hyperf\HttpServer\Annotation\AutoController;

/**
 * HTTP 示例 Controller。
 *
 * 展示 AutoController、参数校验、Service 自动注入和统一成功响应的基础用法。
 */
#[AutoController]
class TestController extends BaseController
{
    /**
     * @var TestService $service
     */
    protected $service;


    /**
     * 示例接口：校验请求参数后调用 TestService，并返回 common-core 统一 JSON 响应。
     */
    public function test()
    {
        $post = $this->request->post();
        $this->checkParams(
            [
                'uid'          => 'required|integer|gt:0',
                'phone'        => 'string',
                'email'        => 'email',
            ],
            $post
        );
        $data = $this->service->test($post);
        return $this->successData($data);
    }
}
