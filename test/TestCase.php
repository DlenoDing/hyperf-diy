<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace HyperfTest;

use Hyperf\Testing\TestCase as HyperfTestCase;

/**
 * 测试基类。
 *
 * Hyperf\Testing\TestCase::setUp() 每个用例都会重建容器并触发 BootApplication(ErrorExceptionHandler 再次 set_error_handler);
 * 用例中首次懒加载翻译/日志等组件也会压入 handler,且部分库以 set_error_handler($prev) 方式"还原"(重复压入同一对象)。
 * PHPUnit 11 会把 handler 栈变化判为 risky,因此在 setUp 前完整记录 handler 栈,tearDown 后原样恢复。
 */
abstract class TestCase extends HyperfTestCase
{
    /** @var array 用例开始前的 error handler 栈(自底向上) */
    private array $handlerStack = [];

    protected function setUp(): void
    {
        $this->handlerStack = self::popAllHandlers();
        self::pushHandlers($this->handlerStack);
        parent::setUp();
    }

    protected function tearDown(): void
    {
        try {
            parent::tearDown();
        } finally {
            self::popAllHandlers();
            self::pushHandlers($this->handlerStack);
        }
    }

    /**
     * 弹出全部 error handler,返回自底向上的列表
     */
    private static function popAllHandlers(): array
    {
        $stack = [];
        for ($i = 0; $i < 500; $i++) {
            $current = set_error_handler(static fn () => false);
            restore_error_handler();
            if ($current === null) {
                break;
            }
            $stack[] = $current;
            restore_error_handler();
        }
        return array_reverse($stack);
    }

    private static function pushHandlers(array $stack): void
    {
        foreach ($stack as $handler) {
            set_error_handler($handler);
        }
    }
}
