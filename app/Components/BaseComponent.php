<?php

declare(strict_types=1);

namespace App\Components;

use Dleno\CommonCore\Base\BaseCoreComponent;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;

/**
 * 项目组件基类。
 *
 * 业务组件统一继承此类，以复用 common-core 组件能力和默认 Redis 客户端。
 */
class BaseComponent extends BaseCoreComponent
{
    /**
     * 默认 Redis 客户端。
     */
    #[Inject]
    protected Redis $redis;
}
