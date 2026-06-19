<?php

declare(strict_types=1);

namespace HyperfTest\Cases;

use Dleno\CommonCore\Tools\Lock\DcsLock;
use Hyperf\Context\ApplicationContext;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Redis\RedisFactory;
use Hyperf\Testing\TestCase;
use Swoole\Coroutine\WaitGroup;

use function Hyperf\Config\config;

/**
 * DcsLock 高并发互斥测试
 *
 * 运行：composer test -- --filter=DcsLockConcurrencyTest
 * 需要本地 redis（由 .env.testing 指向 redis 容器）。
 *
 * @internal
 * @coversNothing
 */
class DcsLockConcurrencyTest extends TestCase
{
    private const LOCK_KEY = 'lock:concurrency:test';

    //并发数控制在连接池(REDIS_MAX_CONNECTION)之内：
    //阻塞抢锁时每个等待协程的 blPop 独占一条 Redis 连接，
    //并发等待数超过连接池会触发「Connection pool exhausted」(属容量规划，非锁缺陷)。
    private const CONCURRENCY = 40;

    private int $inside = 0;
    private int $maxInside = 0;
    private int $violation = 0;
    private int $success = 0;
    private int $fail = 0;
    private int $error = 0;
    private ?string $firstError = null;

    public function testHighConcurrencyMutualExclusion(): void
    {
        $redis = ApplicationContext::getContainer()
            ->get(RedisFactory::class)
            ->get(config('app.dcslock_redis_pool', 'default'));

        //清理历史残留
        $redis->del(self::LOCK_KEY, self::LOCK_KEY . '_WAIT');

        $n       = self::CONCURRENCY;
        $holdMs  = 15;
        $timeout = 15000;

        $wg = new WaitGroup();
        for ($i = 0; $i < $n; $i++) {
            $wg->add();
            Coroutine::create(function () use ($i, $holdMs, $timeout, $wg) {
                try {
                    $this->worker($i, $holdMs, $timeout);
                } catch (\Throwable $e) {
                    $this->error++;
                    if ($this->firstError === null) {
                        $this->firstError = get_class($e) . ': ' . $e->getMessage();
                    }
                } finally {
                    $wg->done();
                }
            });
        }
        $wg->wait(60);
        //等待所有 defer 自动解锁完成
        Coroutine::sleep(0.3);

        $leftLock = (int)$redis->exists(self::LOCK_KEY);

        //断言：高并发下互斥严格成立
        $this->assertSame(0, $this->error, '出现异常（首个：' . $this->firstError . '）');
        $this->assertSame(0, $this->violation, '互斥被破坏：同一时刻有多个协程进入临界区');
        $this->assertLessThanOrEqual(1, $this->maxInside, '最大并发持有超过 1');
        $this->assertSame($n, $this->success, '并非所有协程都成功获取到锁');
        $this->assertSame(0, $this->fail, '阻塞抢锁不应出现失败');
        $this->assertSame(0, $leftLock, '测试结束后仍有残留锁');
    }

    /**
     * 单协程任务：抢锁 -> 进入临界区(校验互斥) -> 持有 -> 协程结束由 defer 自动解锁
     */
    private function worker(int $i, int $holdMs, int $timeout): void
    {
        $uuid = $i . '_' . uniqid('', true);
        $got  = DcsLock::lock(self::LOCK_KEY, $uuid, 3, $timeout);
        if (!$got) {
            $this->fail++;
            return;
        }

        //===== 临界区 =====
        $this->inside++;
        if ($this->inside > $this->maxInside) {
            $this->maxInside = $this->inside;
        }
        if ($this->inside > 1) {
            $this->violation++;
        }
        Coroutine::sleep($holdMs / 1000);
        $this->inside--;
        $this->success++;
        //===== 临界区结束，依赖 DcsLock::lock 注册的 defer 自动释放 =====
    }
}
