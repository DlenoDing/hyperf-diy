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
ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');

error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');

Swoole\Runtime::enableCoroutine(SWOOLE_HOOK_ALL);

! defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__, 1));

require BASE_PATH . '/vendor/autoload.php';

! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', Hyperf\Engine\DefaultOption::hookFlags());

// co-phpunit 在 Swoole 协程中执行 bootstrap，协程环境下 pcntl_fork 被禁用，
// 故使用 ProcScanHandler（基于 proc_open 启动子进程扫描），避免默认 PcntlScanHandler 报 swoole exit。
Hyperf\Di\ClassLoader::init(null, null, new Hyperf\Di\ScanHandler\ProcScanHandler());

$container = require BASE_PATH . '/config/container.php';

$container->get(Hyperf\Contract\ApplicationInterface::class);
