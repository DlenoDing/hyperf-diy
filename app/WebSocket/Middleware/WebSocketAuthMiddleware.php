<?php

declare(strict_types=1);

namespace App\WebSocket\Middleware;

use App\WebSocket\Components\WsAccountComponent;
use App\WebSocket\Conf\WsRequestConf;
use Dleno\CommonCore\Conf\RcodeConf;
use Dleno\CommonCore\Conf\RequestConf;
use Dleno\CommonCore\Exception\Http\HttpException;
use Dleno\CommonCore\Tools\Output\WsOutLog;
use Dleno\CommonCore\Tools\Server;
use Hyperf\Context\Context;
use Hyperf\WebSocketServer\Context as WsContext;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class WebSocketAuthMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

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

        Server::getTraceId();
        $clientToken = get_query_val(WsRequestConf::REQUEST_HEADER_TOKEN, '');
        if (empty($clientToken)) {
            throw new HttpException('Empty Token', RcodeConf::ERROR_TOKEN);
        }

        $request = $request->withHeader(WsRequestConf::REQUEST_HEADER_DEBUG, $debug ? 1 : 0)
                           ->withHeader(WsRequestConf::REQUEST_HEADER_TOKEN, $clientToken);

        try {
            $wsAccountCpt = get_inject_obj(WsAccountComponent::class);
            $account      = $wsAccountCpt->checkAccountByToken($clientToken);
            $accountId    = $account['account_id'] ?? 0;
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

        return $request;
    }
}
