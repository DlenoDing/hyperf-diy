<?php

declare(strict_types=1);

namespace App\Components;

use Dleno\CommonCore\Base\BaseCoreComponent;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;

/**
 * Class BaseComponent
 * @package App\Components
 */
class BaseComponent extends BaseCoreComponent
{
    #[Inject]
    protected Redis $redis;
}
