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
//Hyperf 3.2 logger 结构:['default' => ..., 'channels' => [...]];自定义分组经参数传入(合并进 channels)。
return \Dleno\CommonCore\Logger\LoggerConfig::getDefaultConfig([
    //其他自定义分组
]);
