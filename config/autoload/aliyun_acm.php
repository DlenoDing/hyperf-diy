<?php

return [
    // 是否开启配置中心的接入流程，为 true 时会自动启动一个 AliYunAcmFetcher 进程用于更新配置
    'enable'       => env('ALIYUN_ACM_ENABLE', false),
    // 只能使用进程方式拉取，如果有DB或Redis配置协程方式有问题，废除此项配置
    //'use_standalone_process' => true,
    // 配置更新间隔（秒）
    'interval'     => (int)env('ALIYUN_ACM_INTERVAL', 5),
    // 阿里云 ACM 断点地址，取决于您的可用区
    'endpoint'     => env('ALIYUN_ACM_ENDPOINT', 'acm.aliyun.com'),
    // 当前应用需要接入的 Namespace
    'namespace'    => env('ALIYUN_ACM_NAMESPACE', ''),
    // 您的配置对应的 Data ID
    'data_id'      => env('ALIYUN_ACM_DATA_ID', ''),
    // 您的配置对应的 Group,支持多分组，英文逗号分隔
    'group'        => env('ALIYUN_ACM_GROUP', ''),
    // 您的阿里云账号的 Access Key
    'access_key'   => env('ALIYUN_ACM_AK', ''),
    // 您的阿里云账号的 Secret Key
    'secret_key'   => env('ALIYUN_ACM_SK', ''),
    //存储自定义进程的文件地址，可无需设置
    'process_file' => env('ALIYUN_PROCESS_FILE', BASE_PATH . '/runtime/aliyun.acm.process'),
];

