<?php
namespace App\TaskCron;

use Dleno\CommonCore\Tools\Logger;
use Hyperf\Crontab\Annotation\Crontab;

use function Hyperf\Config\config;

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
     * 当前脚手架默认强制关闭，业务确认逻辑后再删除 return false 或改成 env 开关。
     */
    public function isEnable(): bool
    {
        return false;
        $env = config('app_env');
        if ($env === 'local') {
            return false;
        }
        return true;
    }
}
