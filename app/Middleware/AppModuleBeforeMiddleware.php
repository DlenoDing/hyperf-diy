<?php

namespace App\Middleware;

use Dleno\CommonCore\Middleware\Http\DefaultModuleBeforeMiddleware;
use Dleno\CommonCore\Tools\ApiServer;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;
use Psr\Http\Message\ServerRequestInterface;

/**
 * 项目侧模块前置中间件。
 *
 * 签名校验 / 数据解密 / FOUND 门控 / 白名单与 debug 放行判定均在引擎基类 AbstractModuleBeforeMiddleware（common-core）；
 * 继承包内默认实现 DefaultModuleBeforeMiddleware，本类只覆写项目相关钩子（其余走默认）：
 *   - checkAuth()    ：登录校验，单类内按 ApiServer::isAdminModule() 分流 Admin / API。
 *                      基类已确保「未命中 TOKEN 白名单 且 非（非正式环境 debug）」时才调用本钩子，故无需再判断白名单。
 *   - checkReplay()  ：防重放示例，默认关闭。
 *   - 签名前缀/密钥/时间偏移走 Default 默认（读 config('app.sign_*')），无需覆写。
 *
 * 注册：common-core 已在 InitMiddleware 之后注册 AbstractModuleBeforeMiddleware（同一 HTTP_INIT_MIDDLEWARE_ENABLE 开关）；
 * 本类经 config/autoload/dependencies.php 把 AbstractModuleBeforeMiddleware 绑定到自己即生效（覆盖包内默认）。
 */
class AppModuleBeforeMiddleware extends DefaultModuleBeforeMiddleware
{
    /**
     * 默认 Redis 客户端（供 checkReplay() 防重放占位使用）。
     */
    #[Inject]
    protected Redis $redis;

    /**
     * 登录校验：按模块分流 Admin / API。
     * 白名单 / 非正式环境 debug 的放行已由父类判定，进入本方法即表示需要鉴权；$request 为解密后的请求，可直接取参。
     */
    protected function checkAuth(ServerRequestInterface $request)
    {
        if (ApiServer::isAdminModule()) {
            //后台登录校验接入点（示例，与 API 端体系不同）
            /*$token = get_header_val('Client-Token', 0);
            $checkAuth = get_inject_obj(BlindBoxComponent::class)->checkAuth($token);
            if (!$checkAuth) {
                throw new HttpException('Error Sign', RcodeConf::ERROR_TOKEN);
            }*/
        } else {
            //API 端 token 校验接入点（示例）
            /*$token = get_header_val('Client-Token', 0);
            $checkAuth = get_inject_obj(BlindBoxComponent::class)->checkAuth($token);
            if (!$checkAuth) {
                throw new HttpException('Error Sign', RcodeConf::ERROR_TOKEN);
            }*/
        }
    }

    /**
     * 防重放校验——示例，默认关闭。
     *
     * 返回值约定（由父类 checkSign() 消费）：true=放行；false=判定为重放，框架统一抛错，本方法不抛异常。
     *
     * 去重维度用整段签名 Client-Sign：多客户端并发下不同请求几乎不可能撞同一签名，
     * 而同一已签名请求被原样重放必然产生相同 Client-Sign —— 正是要拦截的对象。
     * 做法：用 SET NX EX 原子占位，占位成功=首次→true 放行，占位失败(key 已存在)=窗口内重复签名=重放→false。
     */
    protected function checkReplay(ServerRequestInterface $request): bool
    {
        //默认不启用防重放：删除下面这行 `return true;` 即开启本示例逻辑。
        return true;

        //==== 以下为开启后的防重放实现（默认因上面 return 不会执行）====
        $sign = (string) get_header_val('Client-Sign', '');
        if ($sign === '') {
            return true;
        }

        //TTL 取 2*signExpire：覆盖墙钟可重放窗口 [now-expire, now+expire]。
        $ttl = max(1, $this->signExpire() * 2);
        //Client-Sign 本身即 32 位 md5，直接加签名前缀隔离业务/环境即可。
        $key = $this->signPrefix() . 'replay:' . $sign;

        //SET NX EX：占位成功(首次)→true 放行；占位失败(key 已存在)→false=重放，交由父类统一抛错。
        return $this->redis->set($key, '1', ['NX', 'EX' => $ttl]) !== false;
    }
}
