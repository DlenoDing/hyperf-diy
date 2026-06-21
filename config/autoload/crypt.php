<?php

declare(strict_types=1);

use function Hyperf\Support\env;

/**
 * 加解密相关配置。RSA 密钥对以 base64(PEM) 存入 .env(单行),此处解码后供调用方取用。
 * 生产请用自己生成的 **2048 位** 密钥对替换；客户端用对应公钥加密随机 AES key。
 * OpenSslRsa 不读 config——由调用方(如 ApiServer)从这里取出私钥后显式传入。
 */
return [
    'rsa' => [
        'private_key' => base64_decode((string) env('RSA_PRIVATE_KEY', '')),
        'public_key'  => base64_decode((string) env('RSA_PUBLIC_KEY', '')),
    ],
];
