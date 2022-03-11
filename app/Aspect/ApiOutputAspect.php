<?php

namespace App\Aspect;

use App\Tools\ApiServer;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\HttpServer\Response;
use Dleno\CommonCore\Conf\GlobalConf;
use Dleno\CommonCore\Tools\Check\CheckVal;
use Dleno\CommonCore\Tools\Crypt\OpenSslCrypt;
use Dleno\CommonCore\Tools\Output\ApiOutLog;
use Dleno\CommonCore\Tools\Server;
use Psr\Http\Message\ResponseInterface;

/**
 * @Aspect
 */
class ApiOutputAspect extends AbstractAspect
{
    // 要切入的类，可以多个，亦可通过 :: 标识到具体的某个方法，通过 * 可以模糊匹配
    public $classes = [
    ];

    // 要切入的注解，具体切入的还是使用了这些注解的类，仅可切入类注解和类方法注解
    public $annotations = [
        \Hyperf\HttpServer\Annotation\AutoController::class,
        \Hyperf\HttpServer\Annotation\Controller::class,
        \Dleno\CommonCore\Annotation\ExceptionHandlerLog::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        // 切面切入后，执行对应的方法会由此来负责
        // $proceedingJoinPoint 为连接点，通过该类的 process() 方法调用原方法并获得结果
        // 在调用前进行某些处理
        $result = $proceedingJoinPoint->process();
        // 在调用后进行某些处理
        //接口输出日志
        ApiOutLog::writeLog($proceedingJoinPoint, $result);

        //接口数据输出加密
        if (config('app.api_data_crypt')) {
            //!(非正式环境debug||白名单)
            $whiteVal = ApiServer::getRouteVal();
            if (!((!Server::isProd() && get_header_val('Client-Debug')) || CheckVal::checkInStatus(
                    GlobalConf::WHITE_TYPE_ENCRYPT,
                    $whiteVal
                ))) {
                if ($result instanceof ResponseInterface) {
                    /** @var Response $result */
                    $output = $result->getBody()
                                     ->getContents();
                    $output = OpenSslCrypt::encrypt($output, ApiServer::getClientAesKey());
                    $result = $result->withBody(new \Hyperf\HttpMessage\Stream\SwooleStream($output));
                } else {
                    $result = OpenSslCrypt::encrypt($result, ApiServer::getClientAesKey());
                }
            }
        }

        return $result;
    }
}
