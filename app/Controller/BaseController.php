<?php
declare(strict_types=1);

namespace App\Controller;

use Dleno\CommonCore\Base\BaseCoreController;
use Dleno\CommonCore\Conf\RcodeConf;
use Dleno\CommonCore\Exception\Http\HttpException;
use Dleno\CommonCore\Tools\Client;
use Dleno\CommonCore\Tools\Server;

class BaseController extends BaseCoreController
{
    /**
     * 处理同一设备多线程并发访问
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
