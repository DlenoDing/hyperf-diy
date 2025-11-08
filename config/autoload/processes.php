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

$processes = [];
if (env('ENABLE_CRONTAB', false)) {
    $processes[] = \Hyperf\Crontab\Process\CrontabDispatcherProcess::class;
}
return $processes;
