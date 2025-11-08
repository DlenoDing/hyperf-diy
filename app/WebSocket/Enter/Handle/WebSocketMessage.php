<?php

namespace App\WebSocket\Enter\Handle;

use Dleno\CommonCore\Conf\RcodeConf;
use Dleno\CommonCore\Conf\RequestConf;
use Dleno\CommonCore\Conf\RpcContextConf;
use Dleno\CommonCore\Exception\Http\HttpException;
use Dleno\CommonCore\Tools\ClassFunc\ClassRoute;
use Dleno\CommonCore\Tools\Server;
use Hyperf\Di\Annotation\Inject;
use Hyperf\ExceptionHandler\ExceptionHandlerDispatcher;
use Hyperf\HttpMessage\Server\Response as Psr7Response;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\HttpServer\Router\Handler;
use Hyperf\WebSocketServer\Exception\Handler\WebSocketExceptionHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Swoole\Websocket\Frame;
use Hyperf\Context\Context;
use Hyperf\WebSocketServer\Context as WsContext;

use function Hyperf\Config\config;
use function Hyperf\Support\env;

/**
 * 消息接收
 * Class WebSocketMessage
 * @package App\WebSocket\Service
 */
class WebSocketMessage
{
    #[Inject]
    protected ExceptionHandlerDispatcher $exceptionHandlerDispatcher;

    /**
     * @var array
     */
    protected $exceptionHandlers;

    /**
     * 消息接收
     * @param \Swoole\Http\Response|\Swoole\WebSocket\Server $server
     * @param Frame $frame
     */
    public function handle($server, Frame $frame): void
    {
        //TODO 初始化
        $this->init();

        //TODO 检查数据格式
        //var_dump($frame->data);

        $frame->data = $this->checkDataFormat($frame->data);
        if ($frame->data === false) {
            goto NOTRETURN;
        }
        //TODO 解析action
        $frame->data['action'] = $this->parseAction($frame->data['action']);

        //TODO 初始化Request（系统默认没有，做兼容模拟处理）
        $request = $this->initRequest($frame->data);

        //var_dump('msg',$request->getAttribute(Dispatched::class)->handler->callback);

        $data = null;
        try {
            //TODO 核心处理
            $this->coreHandle($request, $frame->data);

            //TODO 转换并检查路由
            $this->checkRoute($frame->data['action']);

            //TODO 调用对应Controller,获取返回数据
            $data = get_inject_obj($frame->data['action']['callback'][0])
                ->{$frame->data['action']['callback'][1]}();
        } catch (\Throwable $exception) {
            $data = null;
            $this->initResponse();
            /** @var ResponseInterface $psr7Response */
            $psr7Response = $this->exceptionHandlerDispatcher->dispatch($exception, $this->exceptionHandlers);
            if ($psr7Response instanceof ResponseInterface) {
                $data = $psr7Response->getBody()
                                     ->getContents();
            }
        }

        //回复消息到客户端-压缩支持
        if (!empty($data)) {
            $return = array_to_json(
                [
                    'reqId' => $frame->data['reqId'],
                    'data'  => $data,
                ]
            );
            if (!env('WEBSOCKET_COMPRESSION', false)) {//这个配置无法通过配置中心来设置
                $server->push($frame->fd, $return);
            } else {
                $server->push(
                    $frame->fd,
                    $return,
                    SWOOLE_WEBSOCKET_OPCODE_TEXT,
                    SWOOLE_WEBSOCKET_FLAG_FIN | SWOOLE_WEBSOCKET_FLAG_COMPRESS
                );
            }
        }

        NOTRETURN:
        //不发送任何数据
    }

    private function coreHandle(ServerRequestInterface $request, $data)
    {
        //TODO Hyperf\HttpMessage\Server\Request::stream -> Hyperf\HttpMessage\Stream\SwooleStream
        $stream = new SwooleStream(array_to_json($data['params']));

        //更新对应数据到本次请求模拟的Request
        $request = $request->withBody($stream)
            //TODO Hyperf\HttpMessage\Server\Request::parsedBody
                           ->withParsedBody($data['params']);

        //保存Request上下文
        Context::set(ServerRequestInterface::class, $request);

        if (get_post_val('page')) {
            rpc_context_set(RpcContextConf::PAGE, (int)get_post_val('page'));//页码
        }
        if (get_post_val('perPage')) {
            rpc_context_set(RpcContextConf::PER_PAGE, (int)get_post_val('perPage'));//每页记录数
        }

        return $request;
    }

    private function checkDataFormat($data)
    {
        $data = trim($data, "\r");
        $data = json_to_array($data);
        if (!isset($data['reqId']) || !isset($data['action'])) {
            return false;
        }

        if (!is_string($data['reqId']) && !is_numeric($data['reqId'])) {
            return false;
        }
        $data['params'] = $data['params'] ?? [];

        if (!is_array($data['params'])) {
            $data['params'] = [];
        }

        //记录当前客户端请求ID
        Context::set(RequestConf::REQUEST_REQ_ID, $data['reqId']);

        return $data;
    }

    private function checkRoute(array $action)
    {
        $check = ClassRoute::checkExists($action['callback'][0], $action['callback'][1]);
        if (!$check) {
            throw new HttpException('NOT FOUND', RcodeConf::ERROR_NOTFOUND);
        }
    }

    private function parseAction(string $action)
    {
        $route    = str_replace('.', '/', $action);
        $action   = explode('.', $action);
        $actionCt = count($action);

        $module = [];
        if ($actionCt > 2) {
            $module = array_slice($action, 0, $actionCt - 2);
            array_walk(
                $module,
                function (&$val) {
                    $val = ucfirst($val);
                }
            );
        }

        $ctrl        = get_array_val($action, $actionCt - 2, '');
        $callback[0] = 'App\\WebSocket\\Controller\\';
        if ($module) {
            $callback[0] .= join('\\', $module) . '\\';
        }
        $callback[0] .= ucfirst($ctrl) . 'Controller';
        $callback[1] = get_array_val($action, $actionCt - 1, '');
        return [
            'callback' => $callback,
            'route'    => '/' . $route,
        ];
    }

    private function init()
    {
        $this->exceptionHandlers = config(
            'exceptions.handler.ws',
            [
                WebSocketExceptionHandler::class,
            ]
        );
        //--------记录运行时间和内存占用情况--------
        $runStart = microtime(true);
        $runMem   = memory_get_usage();
        Context::set(RequestConf::REQUEST_RUN_START, $runStart);
        Context::set(RequestConf::REQUEST_RUN_MEM, $runMem);

        //--------请求号，用于标识每个请求---------
        Server::getTraceId();//获取时自动生成
    }

    /**
     * Initialize PSR-7 Request.
     * @return ServerRequestInterface
     */
    private function initRequest($data): ServerRequestInterface
    {
        $request = clone(WsContext::get(ServerRequestInterface::class));

        //TODO Hyperf\HttpMessage\Server\Request::attributes[Dispatched] -> Hyperf\HttpServer\Router\Dispatched
        //这里只能新建Dispatched；不能直接$request->getAttribute(Dispatched::class)再修改，否则会影响握手的Request
        $routes     = [
            1,
            new Handler($data['action']['callback'], $data['action']['route']),
            [],
        ];
        $dispatched = new Dispatched($routes);

        //TODO Hyperf\HttpMessage\Server\Request::uri -> Hyperf\HttpMessage\Uri\Uri
        /** @var UriInterface $uri */
        $uri = $request->getUri()
                       ->withPath($data['action']['route'])
                       ->withQuery('');

        //更新对应数据到本次请求模拟的Request
        $request = $request->withMethod('POST')
                           ->withAttribute(Dispatched::class, $dispatched)
                           ->withUri($uri);

        //保存初始Request上下文
        Context::set(ServerRequestInterface::class, $request);

        return $request;
    }

    /**
     * Initialize PSR-7 Response.
     * @return ResponseInterface
     */
    private function initResponse(): ResponseInterface
    {
        Context::set(ResponseInterface::class, $psr7Response = new Psr7Response());
        return $psr7Response;
    }
}