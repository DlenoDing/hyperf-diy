<?php

namespace App\Controller\Test;

use App\Controller\BaseController;
use App\Service\Test\TestService;
use Hyperf\HttpServer\Annotation\AutoController;

/**
 * @AutoController()
 * Class TestController
 * @package App\Controller
 */
class TestController extends BaseController
{
    /**
     * @var TestService $service
     */
    protected $service;


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