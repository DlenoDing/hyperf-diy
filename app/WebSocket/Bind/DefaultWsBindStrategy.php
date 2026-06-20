<?php

declare(strict_types=1);

namespace App\WebSocket\Bind;

use Dleno\CommonCore\Websocket\Contract\WsBindStrategyInterface;

/**
 * WS 连接绑定策略 —— 脚手架自带的默认实现。
 *
 * 「绑哪些维度、哪些维度可被定向寻址」属于业务决策（各项目不同），故默认实现放在业务端、由 dependencies.php 绑定；
 * common-core 只持有契约 {@see WsBindStrategyInterface}，不再提供包内默认。
 *
 * 本默认实现：**只绑 account_id 一个维度**，并以 account_id 作为唯一可寻址维度
 * —— 即"按用户 account_id 反查其全部在线连接"，覆盖最常见的单端/多连接同账号场景。
 *
 * 需要多端/设备维度时（例如同一账号区分 iOS / Android / Web，能精准推到某一端），
 * 复制本类改成自己的实现（或新建一个），在 config/autoload/dependencies.php 把
 * WsBindStrategyInterface 绑到你的实现即可，无需改动 common-core。
 *
 * 工作原理（由 common-core WsTokenComponent 调用）：
 *  - 连接建立(setBind)时：用 bindDimensions() 拿到本连接要记录的维度集合，写入"正向主绑定"
 *    （<prefix>bind:sfd:<sv>:<fd> => 维度 json），并对 addressableDimensions() 里的每个维度
 *    各建一份"反向索引"（<prefix>bind:<dim>:<value> 的 hash，field=<sv:fd> 唯一标识本连接 => serverFd）。
 *  - 下发(pushToDimMessage)/在线检查时：按 (维度名, 维度值) 取对应反向索引，拿到该维度下的全部连接寻址。
 *  - 断开(unBind)/心跳(refreshBind)时：依据正向主绑定里的维度，反删 / 续期各反向索引。
 */
class DefaultWsBindStrategy implements WsBindStrategyInterface
{
    /**
     * 给定连接与已解析身份，返回本连接要"绑定 + 建反向索引"的维度集合。
     *
     * @param int   $fd       本次连接的 Swoole 文件描述符（连接的本机唯一标识；通常无需用到，
     *                        预留给"维度值依赖具体连接"的特殊策略）。
     * @param array $identity 握手鉴权阶段解析出的身份，至少含：
     *                        - account_id：用户/账号 id（由 WsIdentityResolver 解析、写入握手头后取得）
     *                        - token：本次连接的登录票据
     *                        业务自定义策略若需更多维度（如 device/client_type），
     *                        可让鉴权侧把对应信息放进身份/请求头，并在此读取拼进返回值。
     *
     * @return array dimName => dimValue 的维度集合，例：
     *               - 默认（单端）：['account_id' => 123]
     *               - 多端示例：    ['account_id' => 123, 'device' => 'ios']
     *               维度名即反向索引 key 的一段（<prefix>bind:<dimName>:<dimValue>），
     *               维度值即按该维度寻址时传入的值。返回的全部维度都会写进正向主绑定，
     *               但只有出现在 addressableDimensions() 里的维度才会另外建反向索引（可被寻址）。
     */
    public function bindDimensions(int $fd, array $identity): array
    {
        return [
            'account_id' => $identity['account_id'] ?? 0,
        ];
    }

    /**
     * 声明哪些维度需要建"反向索引"，即哪些维度可被"定向推送 / 在线检查"按值寻址。
     *
     * 必须是 bindDimensions() 返回维度名的子集；不在此列表里的维度只存进正向主绑定、不可被寻址。
     *
     * @return string[] 可寻址的维度名列表，例：
     *                  - 默认（单端）：['account_id']（只支持按 account_id 反查该用户全部连接）
     *                  - 多端示例：    ['account_id', 'device']（既能推给某用户全部端，也能精准推给某一端）
     */
    public function addressableDimensions(): array
    {
        return ['account_id'];
    }
}
