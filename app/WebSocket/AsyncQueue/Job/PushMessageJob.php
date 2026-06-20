<?php

declare(strict_types=1);

namespace App\WebSocket\AsyncQueue\Job;

use Dleno\CommonCore\Tools\Websocket\Job\PushMessageJob as BasePushMessageJob;

/**
 * 推送消息 Job 已下沉 common-core（Dleno\CommonCore\Tools\Websocket\Job\PushMessageJob）。
 * 保留本空子类：兼容历史序列化（队列中以本类名入队的在途任务仍可反序列化执行）；新任务由
 * 下沉后的编排器以 common-core 类入队。项目如需定制可在此 override。
 */
class PushMessageJob extends BasePushMessageJob
{
}
