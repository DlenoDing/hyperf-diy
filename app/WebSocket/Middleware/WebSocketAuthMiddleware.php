<?php

declare(strict_types=1);

namespace App\WebSocket\Middleware;

use App\WebSocket\Conf\WsRequestConf;
use Dleno\CommonCore\Conf\RcodeConf;
use Dleno\CommonCore\Conf\RequestConf;
use Dleno\CommonCore\Exception\Http\HttpException;
use Dleno\CommonCore\Websocket\Support\WsOutLog;
use Dleno\CommonCore\Tools\Server;
use Hyperf\Context\Context;
use Hyperf\WebSocketServer\Context as WsContext;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function Hyperf\Config\config;

class WebSocketAuthMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    #[\Hyperf\Di\Annotation\Inject]
    protected \Dleno\CommonCore\Websocket\Contract\WsHookInterface $wsHook;

    #[\Hyperf\Di\Annotation\Inject]
    protected \Dleno\CommonCore\Websocket\Contract\WsIdentityResolverInterface $identityResolver;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        //服务器固定时区运行
        date_default_timezone_set(config('app.default_time_zone', 'Asia/Shanghai'));

        //--------记录运行时间和内存占用情况--------
        $runStart = microtime(true);
        $runMem   = memory_get_usage();
        Context::set(RequestConf::REQUEST_RUN_START, $runStart);
        Context::set(RequestConf::REQUEST_RUN_MEM, $runMem);

        // 握手检查
        $request = $this->checkHandShake($request);

        //接口输出日志
        WsOutLog::writeLog('HandShake', 'WS-RESPONSE');
        return $handler->handle($request);
    }

    /**
     * 握手验证
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface | false
     */
    private function checkHandShake(ServerRequestInterface $request)
    {
        $debug = get_query_val(WsRequestConf::REQUEST_HEADER_DEBUG, false);
        $debug = ($debug && !Server::isProd()) ? true : false;

        //前置钩子(默认 no-op;可做风控/灰度,抛异常=拒绝握手)
        $this->wsHook->beforeHandshake($request);

        Server::getTraceId();
        $clientToken = get_query_val(WsRequestConf::REQUEST_HEADER_TOKEN, '');
        if (empty($clientToken)) {
            throw new HttpException('Empty Token', RcodeConf::ERROR_TOKEN);
        }

        $request = $request->withHeader(WsRequestConf::REQUEST_HEADER_DEBUG, $debug ? 1 : 0)
                           ->withHeader(WsRequestConf::REQUEST_HEADER_TOKEN, $clientToken);

        $account = [];
        try {
            //身份解析走 common-core 契约(业务实现 AccountIdentityResolver)
            $account   = $this->identityResolver->resolveByToken($clientToken);
            $accountId = $account['account_id'] ?? 0;
            if (empty($accountId)) {
                throw new HttpException('Error Token.', RcodeConf::ERROR_TOKEN);
            }
            $request = $request->withHeader(WsRequestConf::REQUEST_HEADER_ACCOUNT_ID, $accountId);
        } catch (\Throwable $e) {
            throw new HttpException('Error Token', RcodeConf::ERROR_TOKEN);
        }

        //仅Open使用
        Context::set(ServerRequestInterface::class, $request);
        //后续该fd全局使用
        WsContext::set(ServerRequestInterface::class, $request);
        //存完整身份(resolveByToken 返回 + token),供 setBind→WsBindStrategy::bindDimensions 取用自定义维度
        \Dleno\CommonCore\Websocket\Support\WsIdentity::set(
            array_merge($account, ['token' => $clientToken])
        );

        //后置钩子(默认 no-op;身份已解析+Context 已写)
        $this->wsHook->afterHandshake($request, $account);

        return $request;
    }
}
