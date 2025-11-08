<?php

declare(strict_types=1);

namespace App\Process;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;
use Hyperf\Process\ProcessManager;
use Hyperf\Redis\Redis;

use function Hyperf\Config\config;

#[Process(name: "TestProcess", enableCoroutine: true)]
class TestProcess extends AbstractProcess
{
    public string $name = 'TestProcess';

    #[Inject]
    protected Redis $redis;

    public function handle(): void
    {
        go(function () {
            $i = 0;
            while (true) {
                var_dump("co[{$i}].....");
                \Swoole\Coroutine::sleep(3);
                if ($i > 6) {
                    break;
                }
                $i++;
            }
        });
        while (ProcessManager::isRunning()) {
            \Swoole\Coroutine::sleep(3);
            var_dump('11111111');
            \Swoole\Coroutine::sleep(3);
            var_dump('22222222');
            \Swoole\Coroutine::sleep(3);
            var_dump('33333333');
        }
        //$this->process->exit(0);
    }

    public function isEnable($server): bool
    {
        return false;
        $env = config('app_env');
        if ($env === 'local') {
            return false;
        }
        return true;
    }
}
