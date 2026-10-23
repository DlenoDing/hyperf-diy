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

namespace HyperfTest\Cases;

use HyperfTest\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * @internal
 */
#[CoversNothing]
class ExampleTest extends TestCase
{
    /**
     * 根路由健康检查应返回 200 和空内容。
     */
    public function testExample()
    {
        $this->get('/')->assertOk()->assertContent('');
    }
}
