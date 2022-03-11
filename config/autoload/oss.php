<?php

declare(strict_types=1);

return [
    'driver'  => 'tencent',
    'aliyun'  => [
        'access_id'     => env('ALIYUN_OSS_ACCESSID', ''),
        'access_secret' => env('ALIYUN_OSS_SECRET', ''),
        'endpoint'      => env('ALIYUN_OSS_ENDPOINT', ''),
        'bucket'        => env('ALIYUN_OSS_BUCKET', ''),
        'domain'        => env('ALIYUN_OSS_DOMAIN', ''),
    ],
    'tencent' => [
        'secret_id'  => env('TENCENT_OSS_SECRETID', ''),
        'secret_key' => env('TENCENT_OSS_SECRETKEY', ''),
        'region'     => env('TENCENT_OSS_REGION', 'ap-chengdu'),
        'bucket'     => env('TENCENT_OSS_BUCKET', ''),
        'domain'     => env('TENCENT_OSS_DOMAIN', ''),
    ],
];
