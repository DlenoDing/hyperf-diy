<?php

declare(strict_types=1);

namespace App\Service;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;

/**
 * HTTP Service 基类。
 *
 * 放置业务 Service 通用依赖和公共能力，避免每个 Service 重复注入基础组件。
 */
class BaseService
{
    /**
     * 默认 Redis 客户端，供业务 Service 直接访问默认 Redis pool。
     */
    #[Inject]
    public Redis $redis;

}
