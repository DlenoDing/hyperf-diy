<?php

declare(strict_types=1);

namespace App\WebSocket\Middleware;

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
        $debug = get_query_val('Client-Debug', false);
        $debug = ($debug && !Server::isProd()) ? true : false;

        Server::getTraceId();
        $clientToken = get_query_val('Client-Token', '');
        if (empty($clientToken)) {
            throw new HttpException('Empty Token', RcodeConf::ERROR_TOKEN);
        }

        $request = $request->withHeader('Client-Debug', $debug ? 1 : 0)
                           ->withHeader('Client-Token', $clientToken);

        try {
            //检查$clientToken
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            throw new HttpException('Error Token', RcodeConf::ERROR_TOKEN);
        }

        //仅Open使用
        Context::set(ServerRequestInterface::class, $request);
        //后续该fd全局使用
        WsContext::set(ServerRequestInterface::class, $request);
        //Logger::businessLog('HandShake-Request')->info($clientDevice.'::'.'握手完成');

        return $request;
    }
}
