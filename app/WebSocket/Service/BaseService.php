<?php

declare(strict_types=1);

namespace App\WebSocket\Service;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;

/**
 * WS Service 基类。
 *
 * 放置 WS 业务 Service 通用依赖；真实项目可在此增加账号、连接、推送等公共能力。
 */
class BaseService
{
    /**
     * 默认 Redis 客户端，供 WS 业务 Service 直接访问默认 Redis pool。
     */
    #[Inject]
    public Redis $redis;


}
