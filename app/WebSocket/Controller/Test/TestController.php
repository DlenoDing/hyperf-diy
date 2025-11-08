<?php

declare(strict_types=1);

namespace App\WebSocket\Controller\Test;

use App\WebSocket\Controller\BaseController;
use App\WebSocket\Service\Test\TestService;
use Dleno\CommonCore\Annotation\WsController;

#[WsController]
class TestController extends BaseController
{
    /**
     * @var TestService
     */
    protected $service;

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
