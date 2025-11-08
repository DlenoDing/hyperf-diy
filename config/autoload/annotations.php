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
use function Hyperf\Support\env;
use function Hyperf\Support\value;

return [
    'scan' => [
        'paths' => [
            BASE_PATH . '/app',
        ],
        'ignore_annotations' => value(function (){
            $ignore = ['mixin'];
            //未开启TASK功能时，忽略对应注解，避免报错
            if (!env('ENABLE_CRONTAB', false)) {
                $ignore[] = 'Hyperf\Crontab\Annotation\Crontab';
            }
            return $ignore;
        }),
    ],
];
