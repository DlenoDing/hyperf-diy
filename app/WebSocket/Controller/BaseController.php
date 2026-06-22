<?php
declare(strict_types=1);

namespace App\WebSocket\Controller;

use Dleno\CommonCore\Base\BaseCoreController;
use Dleno\CommonCore\Conf\RcodeConf;
use Dleno\CommonCore\Exception\Http\HttpException;
use Dleno\CommonCore\Tools\Client;
use Dleno\CommonCore\Tools\Server;

/**
 * WS Controller 基类。
 *
 * 业务 WS Controller 统一继承此类，以复用 common-core 的请求、响应、校验和分布式锁能力。
 */
class BaseController extends BaseCoreController
{
    /**
     * 限制同一路由、同一设备在短时间内重复并发访问。
     *
     * WS 消息也可能出现客户端快速重复发送；需要幂等或限频的指令可调用此方法。
     *
     * @param int $expire
     * @return bool
     * @throws HttpException
     */
    protected function lockThread($expire = 5)
    {
        $mca           = Server::getRouteMca();
        $mca['module'] = join('_', $mca['module']);
        $mca           = join('_', $mca);
        $device        = Client::getDevice().'';//请求端Device
        $hashKey       = 'Thread_' . $mca . '_' . $device;

        $isLock = $this->lock($hashKey, $device, $expire);
        if (!$isLock) {
            throw new HttpException('Access Frequency Limit', RcodeConf::ERROR_SERVER);
        }
        return true;
    }
}
