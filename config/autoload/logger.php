<?php

declare(strict_types=1);

use Monolog\Formatter;
use Monolog\Logger;

return array_merge(
    \Dleno\CommonCore\Logger\LoggerConfig::getDefaultConfig(),
    [
        //其他自定义配置
    ]
);
