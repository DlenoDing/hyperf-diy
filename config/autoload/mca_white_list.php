<?php

/**
 * 需要特殊处理的路由列表（值位运算）;注意大小写
 * 1 jwt校验跳过
 * 2 接口鉴权跳过
 * 格式：
 * module
 * module.class
 * module.class.function
 */

use Dleno\CommonCore\Conf\GlobalConf;

return [
    //'Admin.Manager.Login'  => GlobalConf::WHITE_TYPE_TOKEN,
    //'*'                    => GlobalConf::WHITE_TYPE_TOKEN,
    'Test'   => GlobalConf::WHITE_TYPE_ENCRYPT | GlobalConf::WHITE_TYPE_SIGN | GlobalConf::WHITE_TYPE_TOKEN,
    'Notify' => GlobalConf::WHITE_TYPE_ENCRYPT | GlobalConf::WHITE_TYPE_SIGN | GlobalConf::WHITE_TYPE_TOKEN,
];
