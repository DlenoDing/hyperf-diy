<?php

declare(strict_types=1);

namespace App\WebSocket\AsyncQueue\Job;

use Dleno\CommonCore\Tools\Websocket\Job\CheckOnlineJob as BaseCheckOnlineJob;

/**
 * 在线检查 Job 已下沉 common-core（Dleno\CommonCore\Tools\Websocket\Job\CheckOnlineJob）。
 * 保留本空子类：兼容历史序列化的在途任务。项目如需定制可在此 override。
 */
class CheckOnlineJob extends BaseCheckOnlineJob
{
}
