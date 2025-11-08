<?php
namespace App\TaskCron;

use Dleno\CommonCore\Tools\Logger;
use Hyperf\Crontab\Annotation\Crontab;

use function Hyperf\Config\config;

#[Crontab(name: "TestCrontab", rule: "*\/5 * * * * *", callback: "execute", enable:"isEnable")]
class TestCrontab
{
    public function execute()
    {
        Logger::stdoutLog()->info(date('Y-m-d H:i:s').'=============');
    }

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
