<?php
namespace App\TaskCron;

use Dleno\CommonCore\Tools\Logger;
use Hyperf\Crontab\Annotation\Crontab;

use function Hyperf\Config\config;
use function Hyperf\Support\env;

/**
 * Crontab 示例任务。
 *
 * 展示如何声明秒级定时任务和动态 enable 方法；当前默认关闭，避免脚手架启动后输出测试日志。
 */
#[Crontab(name: "TestCrontab", rule: "*\/5 * * * * *", callback: "execute", enable:"isEnable")]
class TestCrontab
{
    /**
     * 定时任务执行入口。
     */
    public function execute()
    {
        Logger::stdoutLog()->info(date('Y-m-d H:i:s').'=============');
    }

    /**
     * 控制定时任务是否启用。
     *
     * local 环境始终关闭；非 local 也默认关闭，业务确认逻辑后再改成自己的环境开关。
     */
    public function isEnable(): bool
    {
        if (!env('ENABLE_CRONTAB', false)) {
            return false;
        }

        if (config('app_env') === 'local') {
            return false;
        }

        return false;
    }
}
