<?php

return [
    'rpc_registry' => env('RPC_REGISTRY', false),//是否启用registry模式，否则使用nodes配置
    'nodes' => [
        'local' => [//app_env
            //'Service.Module.ServiceName' => [['port' => 9601, 'host' => 'service-name.xxxx.com']],
        ],
    ],
    'consumers' => value(
        function () {
            //所有服务放入此数组
            $services = [
                // 服务(Interface接口单独建项目，服务端和客户端共用，避免更改时不一致)
                //'Service.Module.ServiceName'      => Common\InterfaceService\Contracts\Module\ServiceNameInterface::class,
            ];


            $consumers = [];
            foreach ($services as $name => $interface) {
                $registry = Dleno\CommonCore\JsonRpc\RpcConsumers::getRegistry($name);
                $node     = Dleno\CommonCore\JsonRpc\RpcConsumers::getNode($name);
                if (empty($node) && empty($registry)) {
                    continue;
                }
                $options     = Dleno\CommonCore\JsonRpc\RpcConsumers::getOptions($name);
                $consumers[] = [
                    // name 需与服务提供者的 name 属性相同
                    'name'          => $name,
                    // 服务接口名，可选，默认值等于 name 配置的值，如果 name 直接定义为接口类则可忽略此行配置，如 name 为字符串则需要配置 service 对应到接口类
                    'service'       => $interface,
                    // 对应容器对象 ID，可选，默认值等于 service 配置的值，用来定义依赖注入的 key
                    'id'            => $interface,
                    // 服务提供者的服务协议，可选，默认值为 jsonrpc-http；可选 jsonrpc-http jsonrpc jsonrpc-tcp-length-check
                    'protocol'      => 'jsonrpc',
                    // 负载均衡算法，可选，默认值为 random
                    'load_balancer' => 'random',
                    // 这个消费者要从哪个服务中心获取节点信息，如不配置则不会从服务中心获取节点信息
                    'registry'      => $registry,
                    // 如果没有指定上面的 registry 配置，即为直接对指定的节点进行消费，通过下面的 nodes 参数来配置服务提供者的节点信息
                    'nodes'         => $node,
                    // 配置项，会影响到 Packer 和 Transporter
                    'options'       => $options,
                ];
            }
            return $consumers;
        }
    ),
];
