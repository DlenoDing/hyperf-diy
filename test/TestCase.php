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
 * Hyperf\Testing\TestCase::setUp() 每个用例都会重建容器并触发 BootApplication,
 * ErrorExceptionHandler 监听器随之再 set_error_handler 一次;PHPUnit 11 会把未还原的 handler 判为 risky。
 * 这里记录 setUp 期间新压入的 handler 数,tearDown 时逐个弹出,保持 handler 栈与用例开始前一致。
 */
abstract class TestCase extends HyperfTestCase
{
    private int $pushedErrorHandlers = 0;

    protected function setUp(): void
    {
        $before = $this->currentErrorHandler();
        parent::setUp();
        $this->pushedErrorHandlers = $this->currentErrorHandler() === $before ? 0 : 1;
    }

    protected function tearDown(): void
    {
        try {
            parent::tearDown();
        } finally {
            for (; $this->pushedErrorHandlers > 0; --$this->pushedErrorHandlers) {
                restore_error_handler();
            }
        }
    }

    private function currentErrorHandler(): mixed
    {
        $handler = set_error_handler(static fn () => false);
        restore_error_handler();
        return $handler;
    }
}
