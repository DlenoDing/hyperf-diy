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
return [
    'handler' => value(
        function () {
            $handler = [];
            if (env('ENABLE_HTTP', false)) {
                $handler['http'] = [
                    \Dleno\CommonCore\Exception\Handler\CommonExceptionHandler::class,//公共异常控制器，不中断
                    \Dleno\CommonCore\Exception\Handler\Http\HttpExceptionHandler::class,//http异常控制器
                    \Dleno\CommonCore\Exception\Handler\Http\ValidationExceptionHandler::class,//验证器异常控制器
                    \Dleno\CommonCore\Exception\Handler\Rpc\RpcClientRequestExceptionHandler::class,//rpc-client请求异常
                    \Dleno\CommonCore\Exception\Handler\Http\AppExceptionHandler::class,//APP异常控制器
                    \Dleno\CommonCore\Exception\Handler\Http\ServerExceptionHandler::class,//系统异常控制器
                    \Dleno\CommonCore\Exception\Handler\Http\DefaultExceptionHandler::class,//默认异常控制器
                ];
            }
            if (env('ENABLE_WS', false)) {
                $handler['ws'] = [
                    \Dleno\CommonCore\Exception\Handler\CommonExceptionHandler::class,//公共异常控制器，不中断
                    \Dleno\CommonCore\Exception\Handler\Websocket\HttpExceptionHandler::class,//http异常控制器
                    \Dleno\CommonCore\Exception\Handler\Websocket\ValidationExceptionHandler::class,//验证器异常控制器
                    \Dleno\CommonCore\Exception\Handler\Rpc\RpcClientRequestExceptionHandler::class,//rpc-client请求异常
                    \Dleno\CommonCore\Exception\Handler\Websocket\AppExceptionHandler::class,//APP异常控制器
                    \Dleno\CommonCore\Exception\Handler\Websocket\ServerExceptionHandler::class,//系统异常控制器
                    \Dleno\CommonCore\Exception\Handler\Websocket\DefaultExceptionHandler::class,//默认异常控制器
                ];
            }
            if (env('ENABLE_RPC', false)) {
                $handler['jsonrpc'] = [
                    \Dleno\CommonCore\Exception\Handler\CommonExceptionHandler::class,//公共异常控制器，不中断
                    \Dleno\CommonCore\Exception\Handler\Rpc\RpcClientRequestExceptionHandler::class,//rpc-client请求异常
                    \Dleno\CommonCore\Exception\Handler\Rpc\AppExceptionHandler::class,//APP异常控制器
                    \Dleno\CommonCore\Exception\Handler\Rpc\ServerExceptionHandler::class,//系统异常控制器
                    \Dleno\CommonCore\Exception\Handler\Rpc\DefaultExceptionHandler::class,//默认异常控制器
                ];
            }
            return $handler;
        }
    ),
];
