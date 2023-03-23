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

namespace App\RpcService\Test;

use Hyperf\RpcServer\Annotation\RpcService;

/**
 * Class CmdPushService.
 * @RpcService(name="Service.Test.TestService", protocol="jsonrpc", server="jsonrpc", publishTo="consul1")
 */
class TestService implements TestServiceInterface
{
    /**
     * @param int $a
     * @param int $b
     * @return int
     */
    public function test1(int $a, int $b): int
    {
        return $a + $b;
    }

    /**
     * @return bool
     */
    public function test2(): bool
    {
        return false;
    }
}
