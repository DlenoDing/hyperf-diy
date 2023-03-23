<?php

return [
    //异常追踪机器人配置(为空或对应配置不存在)
    'trace'   => 'trace',
    'redis'   => 'default',
    'configs' => [
        'default' => [
            'enable'    => env('DINGTALK_ROBOT_ENABLE', false),
            'name'      => env('DINGTALK_ROBOT_NAME', '系统通知'),
            'frequency' => env('DINGTALK_ROBOT_FREQUENCY', 60),
            'configs'   => [
                //单个机器人有消息频率限制，可以根据需要添加多个机器人来发送消息
                [
                    'token'  => env('DINGTALK_ROBOT_01_TOKEN', ''),
                    'secret' => env('DINGTALK_ROBOT_01_SECRET', ''),
                ],
                [
                    'token'  => env('DINGTALK_ROBOT_02_TOKEN', ''),
                    'secret' => env('DINGTALK_ROBOT_02_SECRET', ''),
                ],
            ],
        ],
        'trace'   => [
            'enable'    => env('DINGTALK_TRACE_ENABLE', false),
            'name'      => env('DINGTALK_TRACE_NAME', '异常追踪'),
            'frequency' => env('DINGTALK_TRACE_FREQUENCY', 60),
            'configs'   => [
                [
                    'token'  => env('DINGTALK_TRACE_01_TOKEN', ''),
                    'secret' => env('DINGTALK_TRACE_01_SECRET', ''),
                ],
                [
                    'token'  => env('DINGTALK_TRACE_02_TOKEN', ''),
                    'secret' => env('DINGTALK_TRACE_02_SECRET', ''),
                ],
            ],
        ],
        //需要的其他机器人自行配置里加入（也可以动态方式加入并使用）
    ],

];
