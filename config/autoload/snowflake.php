<?php

declare(strict_types=1);

use Hyperf\Snowflake\MetaGenerator\RedisMilliSecondMetaGenerator;
use Hyperf\Snowflake\MetaGenerator\RedisSecondMetaGenerator;
use Hyperf\Snowflake\MetaGeneratorInterface;

return [
    //雪花 ID 起始秒，默认使用 Hyperf 官方默认值。
    'begin_second'                       => MetaGeneratorInterface::DEFAULT_BEGIN_SECOND,
    //毫秒级 Redis 元数据生成器使用默认 Redis pool。
    RedisMilliSecondMetaGenerator::class => [
        'pool' => 'default',
    ],
    //秒级 Redis 元数据生成器使用默认 Redis pool。
    RedisSecondMetaGenerator::class      => [
        'pool' => 'default',
    ],
];
