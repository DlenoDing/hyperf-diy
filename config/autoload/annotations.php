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
use Hyperf\Utils\Coroutine;

return [
    'scan' => [
        'paths' => [
            BASE_PATH . '/app',
        ],
        'ignore_annotations' => value(function (){
            $ignore = ['mixin'];
            //未开启RPC时，忽略对应注解，避免报错
            if (!env('ENABLE_RPC', false)) {
                $ignore[] = 'Hyperf\RpcServer\Annotation\RpcService';
            }
            //未开启TASK功能时，忽略对应注解，避免报错
            if (intval(env('TASK_WORK_NUM', -1)) == -1) {
                $ignore[] = 'Hyperf\Task\Annotation\Task';
            }
            return $ignore;
        }),
        'class_map' => [
        ],
    ],
];
