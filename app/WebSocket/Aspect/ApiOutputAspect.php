<?php

namespace App\WebSocket\Aspect;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Dleno\CommonCore\Tools\Output\WsOutLog;

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
        \Dleno\CommonCore\Annotation\WsController::class,
        \Dleno\CommonCore\Annotation\WsExceptionHandlerLog::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {

        // 切面切入后，执行对应的方法会由此来负责
        // $proceedingJoinPoint 为连接点，通过该类的 process() 方法调用原方法并获得结果
        // 在调用前进行某些处理
        $result = $proceedingJoinPoint->process();
        // 在调用后进行某些处理
        //接口输出日志
        if ((new \ReflectionMethod($proceedingJoinPoint->className, $proceedingJoinPoint->methodName))->isPublic()) {
            WsOutLog::writeLog($result, 'WS-RESPONSE');
        }

        return $result;
    }
}
