<?php

namespace App\Aspect;

use App\Components\Admin\BlindBox\BlindBoxComponent;
use App\Conf\ManagerRedisKeyConf;
use App\Conf\SignConf;
use App\Tools\ApiServer;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Redis\Redis;
use Hyperf\Utils\Context;
use Dleno\CommonCore\Conf\GlobalConf;
use Dleno\CommonCore\Conf\RcodeConf;
use Dleno\CommonCore\Exception\Http\HttpException;
use Dleno\CommonCore\Tools\Check\CheckVal;
use Dleno\CommonCore\Tools\Crypt\OpenSslCrypt;
use Dleno\CommonCore\Tools\Logger;
use Dleno\CommonCore\Tools\Server;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @Aspect
 */
class AdminModuleBeforeAspect extends AbstractAspect
{
    // 要切入的类，可以多个，亦可通过 :: 标识到具体的某个方法，通过 * 可以模糊匹配
    public $classes = [];

    // 要切入的注解，具体切入的还是使用了这些注解的类，仅可切入类注解和类方法注解
    public $annotations = [
        \Hyperf\HttpServer\Annotation\AutoController::class,
        \Hyperf\HttpServer\Annotation\Controller::class,
    ];

    /**
     * @Inject()
     * @var Redis
     */
    public $redis;

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        // 在调用前进行某些处理
        if (ApiServer::isAdminModule()) {//后台核心处理及校验
            //var_dump('admin');
            //获取当前路由白名单值
            $whiteVal = ApiServer::getRouteVal();
            //验证接口签名
            $this->checkSign($whiteVal);
            //接口数据解密
            $this->dataDecryption($whiteVal);
            //验证登录
            $this->checkAuth($whiteVal);
        }
        $result = $proceedingJoinPoint->process();
        // 在调用后进行某些处理

        return $result;
    }

    /**
     * 接口数据解密
     */
    private function dataDecryption($whiteVal)
    {
        //关闭加密功能
        if (!config('app.api_data_crypt')) {
            return;
        }
        //白名单
        if (CheckVal::checkInStatus(GlobalConf::WHITE_TYPE_ENCRYPT, $whiteVal)) {
            return;
        }
        $request     = get_inject_obj(RequestInterface::class);
        $postRawBody = $request->getBody()
                               ->getContents();
        //加密数据处理
        $isJson = CheckVal::isJson($postRawBody);
        if (!$isJson && !empty($postRawBody)) {
            //获取当前请求的aes key
            $aesKey = ApiServer::getClientAesKey();
            if (empty($aesKey)) {
                throw new HttpException('Bad Request', RcodeConf::ERROR_BAD);
            }
            //aes解密$rawBody
            $postRawBody = OpenSslCrypt::decrypt($postRawBody, $aesKey);
        }
        $post = $postRawBody ? json_to_array($postRawBody) : [];
        //!(非正式环境debug||白名单)
        if (!((!Server::isProd() && get_header_val('Client-Debug')) ||
              CheckVal::checkInStatus(GlobalConf::WHITE_TYPE_ENCRYPT, $whiteVal))) {
            //是明文||不是明文但不能解密（空忽略）
            if ($isJson || (!$isJson && !empty($postRawBody) && empty($post))) {
                throw new HttpException('Bad Request', RcodeConf::ERROR_BAD);
            }
        }
        //将解密后的参数赋值给request parsedBody
        $request = $request->withParsedBody($post);
        Context::set(ServerRequestInterface::class, $request);
    }

    /**
     * 验证接口签名
     */
    private function checkSign($whiteVal)
    {
        //开关检查
        if (!config('app.api_check_sigin')) {
            return;
        }
        //非正式环境debug
        if (!Server::isProd() && get_header_val('Client-Debug')) {
            return;
        }
        //白名单
        if (CheckVal::checkInStatus(GlobalConf::WHITE_TYPE_SIGN, $whiteVal)) {
            return;
        }
        $traceId = Server::getTraceId();
        //检查时间有效期
        $now  = time();
        $time = get_header_val('Client-Timestamp', 0);
        $time = strlen($time) > 10 ? substr($time, 0, 10) : $time;
        if ($time < $now - SignConf::EXPIRE_TIME || $time > $now + SignConf::EXPIRE_TIME) {
            Logger::systemLog('SIGN')
                  ->debug(
                      sprintf('Trace-Id::%s||Message::%s', $traceId, "鉴权时间戳无效：[{$now}|{$time}]")
                  );
            throw new HttpException('Error Sign', RcodeConf::ERROR_SIGN);
        }

        $postRawBody = get_inject_obj(RequestInterface::class)
            ->getBody()
            ->getContents();

        //验证签名
        $str  = SignConf::PREFIX .
                get_header_val('Client-Device', '') .
                get_header_val('Client-Os', '') .
                get_header_val('Client-AppId', '') .
                get_header_val('Client-Version', '') .
                get_header_val('Client-Timestamp', '') .
                get_header_val('Client-Nonce', '') .
                SignConf::SIGN_KEY .
                $postRawBody .
                get_header_val('Client-Token', '');
        $sign = md5($str);
        if (get_header_val('Client-Sign', '') <> $sign) {
            Logger::systemLog('SIGN')
                  ->debug(
                      sprintf(
                          'Trace-Id::%s||Message::%s||SignStr::%s',
                          $traceId,
                          "签名无效：[{$sign}|" . get_header_val('Client-Sign', '') . "]",
                          $str
                      )
                  );
            throw new HttpException('Error Sign', RcodeConf::ERROR_SIGN);
        }
    }

    /**
     * 检查用户登录状态
     * @param $whiteVal
     */
    private function checkAuth($whiteVal)
    {
        $token = get_header_val('Client-Token', 0);;
//        get_inject_obj(BlindBoxComponent::class)->checkAuth($token);
    }
}
