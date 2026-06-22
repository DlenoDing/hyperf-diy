<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace HyperfTest;

use Hyperf\Testing\Client;
use PHPUnit\Framework\TestCase;

use function Hyperf\Support\make;

/**
 * HTTP 测试基类。
 *
 * 封装 Hyperf Testing Client，便于测试用例直接调用 get/post/json 等方法。
 *
 * @method get($uri, $data = [], $headers = [])
 * @method post($uri, $data = [], $headers = [])
 * @method json($uri, $data = [], $headers = [])
 * @method file($uri, $data = [], $headers = [])
 * @method request($method, $path, $options = [])
 */
abstract class HttpTestCase extends TestCase
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * 初始化 Hyperf 测试客户端。
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->client = make(Client::class);
    }

    /**
     * 将 get/post/json/file/request 等调用代理到 Hyperf 测试客户端。
     */
    public function __call($name, $arguments)
    {
        return $this->client->{$name}(...$arguments);
    }
}
