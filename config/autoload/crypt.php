<?php

declare(strict_types=1);

use function Hyperf\Support\env;

/**
 * 加解密相关配置。RSA 密钥对以 **base64(PEM)** 存储(.env 单行 → 此处原样保留 base64,
 * 两系统间传输统一用 base64;OpenSslRsa 用前内部解码)。
 * 生产请用自己生成的 **2048 位** 密钥对替换；客户端用对应公钥加密随机 AES key。
 * OpenSslRsa 不读 config——由调用方(如 ApiServer)从这里取出后显式传入。
 */
return [
    'rsa' => [
        'private_key' => (string) env('RSA_PRIVATE_KEY', ''),
        'public_key'  => (string) env('RSA_PUBLIC_KEY', ''),
    ],
];
