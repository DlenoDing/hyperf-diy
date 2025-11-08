<?php

declare(strict_types=1);

namespace App\WebSocket\Service;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;

class BaseService
{
    #[Inject]
    public Redis $redis;


}
