<?php

declare(strict_types=1);

namespace App\Process;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;
use Hyperf\Process\ProcessManager;
use Hyperf\Redis\Redis;

use function Hyperf\Config\config;

/**
 * 自定义 Process 示例。
 *
 * 展示 Hyperf Process 中启动协程、循环执行和响应进程退出信号的基础写法。
 * 当前脚手架默认关闭，避免启动后输出测试日志。
 */
#[Process(name: "TestProcess", enableCoroutine: true)]
class TestProcess extends AbstractProcess
{
    /**
     * 进程名称，便于日志和进程列表识别。
     */
    public string $name = 'TestProcess';

    /**
     * 示例 Redis 依赖，业务 Process 可直接注入需要的组件。
     */
    #[Inject]
    protected Redis $redis;

    /**
     * Process 主逻辑入口。
     */
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

    /**
     * 控制示例 Process 是否启用。
     *
     * local 环境始终关闭；非 local 也默认关闭，业务确认逻辑后再改成自己的环境开关。
     */
    public function isEnable($server): bool
    {
        if (config('app_env') === 'local') {
            return false;
        }

        return false;
    }
}
