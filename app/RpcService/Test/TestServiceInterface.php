<?php

namespace App\RpcService\Test;

/**
 * todo 实际应用中应将Interface文件定义在公共包中
 * Interface TestServiceInterface
 * @package App\RpcService\Test
 */
interface TestServiceInterface
{
    /**
     * @param int $a
     * @param int $b
     * @return int
     */
    public function test1(int $a, int $b): int;

    /**
     * @return bool
     */
    public function test2(): bool;
}