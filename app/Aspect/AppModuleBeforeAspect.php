<?php

namespace App\Aspect;

use Dleno\CommonCore\Aspect\AbstractModuleBeforeAspect;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;

use function Hyperf\Config\config;

/**
 * 项目侧模块前置切面中间类：将 common-core 通用切面所需的签名参数钩子接到本项目配置。
 *（签名密钥/前缀/时间偏移统一走 config('app.sign_*')，由 env 注入；
 *  路由白名单值 / AES 密钥已统一由 common-core ApiServer 提供，无需此处再接）
 *
 * 仍为抽象类，isMatch()/checkAuth() 留给 Admin / Api 具体子类实现。
 */
abstract class AppModuleBeforeAspect extends AbstractModuleBeforeAspect
{
    /**
     * 默认 Redis 客户端（供 checkReplay() 防重放占位使用）。
     */
    #[Inject]
    protected Redis $redis;

    /**
     * 签名前缀
     */
    protected function signPrefix(): string
    {
        return (string) config('app.sign_prefix', 'WS_');
    }

    /**
     * 签名密钥
     */
    protected function signKey(): string
    {
        return (string) config('app.sign_key', '');
    }

    /**
     * 签名允许的时间偏移量（秒）
     */
    protected function signExpire(): int
    {
        return (int) config('app.sign_expire', 300);
    }

    /**
     * 防重放校验（覆写 common-core 默认实现）——示例，默认关闭。
     *
     * 返回值约定（由 common-core checkSign() 消费）：true=放行；false=判定为重放，框架统一抛错，本方法不抛异常。
     *
     * 调用时机由父类 checkSign() 在「签名校验通过后」触发。
     * 去重维度用整段签名 Client-Sign（而非 Client-Nonce）：Client-Sign 由
     *   设备/系统/AppId/版本/时间戳/Client-Nonce/signKey/body/Client-Token 一起 md5 得出，
     * 多客户端并发下「不同请求几乎不可能撞同一签名」，远比客户端自生成、可能撞值的 Client-Nonce 可靠；
     * 而「同一已签名请求被原样重放」必然产生相同 Client-Sign —— 正是要拦截的对象。
     * 做法：用 SET NX EX 原子占位，占位成功=首次→true 放行，占位失败(key 已存在)=窗口内重复签名=重放→false。
     */
    protected function checkReplay(): bool
    {
        //默认不启用防重放：删除下面这行 `return true;` 即开启本示例逻辑。
        return true;

        //==== 以下为开启后的防重放实现（默认因上面 return 不会执行）====
        //父类已校验签名通过才会走到这里，Client-Sign 必非空；此处仅作兜底放行。
        $sign = (string) get_header_val('Client-Sign', '');
        if ($sign === '') {
            return true;
        }

        //TTL 取 2*signExpire：被签名的时间戳在 [now-expire, now+expire] 内都通过父类时间校验，
        //最坏情况一条请求可被重放的墙钟跨度达 2*expire，占位需留存这么久才能覆盖整个可重放窗口。
        $ttl = max(1, $this->signExpire() * 2);
        //Client-Sign 本身即 32 位 md5，直接加签名前缀隔离业务/环境即可，无需再 hash。
        $key = $this->signPrefix() . 'replay:' . $sign;

        //SET NX EX：占位成功(首次)返回 true 放行；占位失败(key 已存在)返回 false=窗口内重复签名=重放，
        //交由 common-core checkSign() 统一抛错。
        return $this->redis->set($key, '1', ['NX', 'EX' => $ttl]) !== false;
    }
}
