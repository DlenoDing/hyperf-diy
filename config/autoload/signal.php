<?php

declare(strict_types=1);

return [
    'handlers' => [
        // 业务自定义 signal handler 写在这里。
        // common-core 已自动注册 ProcessStopHandler，不要在这里重复添加。
    ],

    // 如需调整信号等待超时时间，可在业务项目中打开此配置。
    // common-core 不定义 timeout，避免 array_merge_recursive 把标量合并成数组。
    // 'timeout' => 5.0,
];
