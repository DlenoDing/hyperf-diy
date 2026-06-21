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
    'handler' => value(
        function () {
            $handler = [];
            if (env('ENABLE_HTTP', false)) {
                $handler['http'] = [
                    \Dleno\CommonCore\Exception\Handler\CommonExceptionHandler::class,//公共异常控制器，不中断
                    \Dleno\CommonCore\Exception\Handler\Http\HttpExceptionHandler::class,//http异常控制器
                    \Dleno\CommonCore\Exception\Handler\Http\ValidationExceptionHandler::class,//验证器异常控制器
                    \Dleno\CommonCore\Exception\Handler\Http\RpcClientRequestExceptionHandler::class,//rpc-client请求异常(http链须用Http版:会写JSON错误体;Rpc版只原样返回→HTTP下客户端收空body)
                    \Dleno\CommonCore\Exception\Handler\Http\AppExceptionHandler::class,//APP异常控制器
                    \Dleno\CommonCore\Exception\Handler\Http\ServerExceptionHandler::class,//系统异常控制器
                    \Dleno\CommonCore\Exception\Handler\Http\DefaultExceptionHandler::class,//默认异常控制器
                ];
            }
            if (env('ENABLE_WS', false)) {
                $handler['ws'] = [
                    \Dleno\CommonCore\Exception\Handler\CommonExceptionHandler::class,//公共异常控制器，不中断
                    \Dleno\CommonCore\Websocket\Exception\Handler\HttpExceptionHandler::class,//http异常控制器
                    \Dleno\CommonCore\Websocket\Exception\Handler\ValidationExceptionHandler::class,//验证器异常控制器
                    \Dleno\CommonCore\Websocket\Exception\Handler\RpcClientRequestExceptionHandler::class,//rpc-client请求异常
                    \Dleno\CommonCore\Websocket\Exception\Handler\AppExceptionHandler::class,//APP异常控制器
                    \Dleno\CommonCore\Websocket\Exception\Handler\ServerExceptionHandler::class,//系统异常控制器
                    \Dleno\CommonCore\Websocket\Exception\Handler\DefaultExceptionHandler::class,//默认异常控制器
                ];
            }
            return $handler;
        }
    ),
];
