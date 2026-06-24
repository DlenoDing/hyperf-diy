# dleno/hyperf-diy

`dleno/hyperf-diy` 是基于 Hyperf 3.1 和 `dleno/common-core` 的业务项目脚手架。它不是一个纯净 Hyperf 空项目，而是预置了 Dleno 项目常用的 HTTP、WebSocket、AMQP、AsyncQueue、Crontab、Model 分表、分布式锁、接口签名/加密、日志和测试示例。

项目定位：

- 作为新业务服务的起始模板。
- 展示 `dleno/common-core` 在真实项目里的推荐接入方式。
- 提供 HTTP / WS 双协议项目的目录结构、配置模板和示例代码。
- 保留可运行的基础测试，用于验证 common-core 关键能力。

## 环境要求

- PHP `>= 8.1`
- Swoole `>= 5.0`
- Hyperf `~3.1`
- MySQL
- Redis
- WebSocket 功能要求 Redis `7.4+`，因为 common-core 的 WS 连接绑定和 presence 在线索引依赖 `HEXPIRE`
- RabbitMQ 仅在启用 AMQP 示例时需要

启用 WebSocket 时必须使用 `SWOOLE_BASE` 模式。本项目 `config/autoload/server.php` 默认已经设置为 `SWOOLE_BASE`。

## 创建项目

```bash
composer create-project dleno/hyperf-diy path/to/install
cd path/to/install
```

如果是 clone 源码，或使用 `composer create-project --no-install` 创建项目，再执行依赖安装：

```bash
composer install
```

本项目使用 `dleno/hyperf-env-multi` 管理环境配置。创建项目后会尝试复制 `.env.local.example` 到 `.env.local`。加载顺序固定为：先加载 `.env`，再根据 `APP_ENV` 加载 `.env.<APP_ENV>`；环境文件中的同名变量以后者为准。例如 `APP_ENV=local` 时，`.env.local` 会覆盖 `.env` 中的同名配置。common-core 会在自己的 `ConfigProvider` 读取 `env()` 前显式调用 `EnvLoader::load(BASE_PATH)`，因此 `ENABLE_HTTP` / `ENABLE_WS` 等早期配置也会读取到环境覆盖后的值。实际部署时请按环境准备 `.env` / `.env.<APP_ENV>`，不要把生产密钥、数据库密码、RSA 私钥提交到仓库。

## 快速启动

最小 HTTP 启动配置：

```dotenv
APP_ENV=local
ENABLE_HTTP=true
HTTP_PORT=9504
ENABLE_WS=false
ENABLE_CRONTAB=false
AMQP_ENABLE=false

REDIS_HOST=localhost
REDIS_PORT=6379
REDIS_DB=0

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=test
DB_USERNAME=root
DB_PASSWORD=root
```

启动服务：

```bash
composer start
```

健康检查：

```bash
curl http://127.0.0.1:9504/
```

返回空字符串且 HTTP 200 表示 HTTP server 正常。

## 目录结构

```text
app/                         业务示例代码
app/Controller               HTTP Controller 示例
app/Service                  HTTP Service 示例
app/Components               业务组件 / 本地缓存 / Redis 缓存示例
app/Model                    BaseModel、普通模型、分表模型示例
app/Middleware               模块前置中间件示例（未知路由 404 / 签名 / 解密 / 登录鉴权）
app/Amqp                     AMQP Producer / Consumer 示例
app/AsyncQueue               AsyncQueue Job / Consumer 示例
app/Process                  自定义 Process 示例
app/TaskCron                 Crontab 示例
app/WebSocket                WebSocket 业务接入示例
config/autoload              Hyperf 配置
test                         PHPUnit / co-phpunit 测试
runtime                      运行时目录
ws-test                      WebSocket 调试资源
```

## common-core 接入点

本项目依赖 `dleno/common-core`，并在 `config/autoload/dependencies.php` 中完成关键绑定：

```php
Hyperf\HttpMessage\Server\RequestParserInterface::class
    => Dleno\CommonCore\Core\Request\RequestParser::class,
Hyperf\HttpServer\Contract\RequestInterface::class
    => Dleno\CommonCore\Core\Request\Request::class,

Dleno\CommonCore\Middleware\Http\AbstractModuleBeforeMiddleware::class
    => App\Middleware\AppModuleBeforeMiddleware::class,

Dleno\CommonCore\Websocket\Contract\WsBindStrategyInterface::class
    => App\WebSocket\Bind\DefaultWsBindStrategy::class,
Dleno\CommonCore\Websocket\Contract\WsHookInterface::class
    => App\WebSocket\Hook\AppWsHook::class,
```

其中：

- HTTP 请求解析和请求对象必须由业务项目配置覆盖，保证优先级高于 Hyperf 默认绑定。
- HTTP 模块前置中间件由 common-core 自动注册抽象基类，本项目绑定到 `AppModuleBeforeMiddleware`，用于接入登录校验和防重放示例；不绑定时会走 common-core 默认实现，签名/解密仍按开关执行，但业务登录校验为 no-op。
- WebSocket 绑定策略属于业务决策，`WsBindStrategyInterface` 必须由业务项目绑定；`WsHookInterface` 在 common-core 中有 no-op 默认实现，本项目覆盖它是为了展示登录态解析、握手鉴权和生命周期钩子的推荐接入方式。
- HTTP / WS 基础中间件由 common-core `ConfigProvider` 根据 `ENABLE_HTTP` / `ENABLE_WS` 自动注入，业务不要重复追加同名中间件；如要接管，先关闭对应 env 开关。
- HTTP / WS 输出日志切面由 common-core 自动注册，业务项目不需要声明自己的 `ApiOutputAspect`。
- 默认 HTTP / WS 异常链由 common-core `ExceptionHandlerConfig` 生成；`config/autoload/exceptions.php` 保留为业务 handler 的顺序控制入口。

异常处理器追加示例：

```php
use Dleno\CommonCore\Exception\ExceptionHandlerConfig;

return [
    'handler' => ExceptionHandlerConfig::defaultHandlers(
        httpCommonHandlers: [
            App\Exception\Handler\BusinessCommonExceptionHandler::class,
        ],
        wsCommonHandlers: [
            App\WebSocket\Exception\Handler\BusinessCommonWsExceptionHandler::class,
        ],
        httpBeforeDefault: [
            App\Exception\Handler\BusinessOutputExceptionHandler::class,
        ],
        wsBeforeDefault: [
            App\WebSocket\Exception\Handler\BusinessWsOutputExceptionHandler::class,
        ],
    ),
];
```

公共前置 handler 放到 `httpCommonHandlers` / `wsCommonHandlers`，会插入到 common-core 的 `CommonExceptionHandler` 之后，适合回滚、审计、上下文清理等不中断传播的处理；业务输出类 handler 放到 `httpBeforeDefault` / `wsBeforeDefault`，会插入到 common-core 的兜底 `DefaultExceptionHandler` 之前。不要把业务 handler 追加在默认链之后，否则异常可能已经被兜底 handler 截断。

## HTTP 示例

入口示例：

- `app/Controller/BaseController.php`
- `app/Controller/Test/TestController.php`
- `app/Service/Test/TestService.php`
- `app/Components/Test/TestComponent.php`

能力说明：

- Controller 继承 `BaseCoreController`。
- Controller 会按命名约定自动注入对应 Service。
- `checkParams()` 使用 Hyperf validation 规则校验请求参数。
- `successData()` 输出 common-core 统一 JSON 格式。
- `lockThread()` 展示按路由和设备号做并发访问限制。
- `app/Middleware/AppModuleBeforeMiddleware` 展示接口签名、解密、登录鉴权的接入点（单类内按 `ApiServer::isAdminModule()` 分流 API/Admin）。继承 common-core `DefaultModuleBeforeMiddleware` 并在 `config/autoload/dependencies.php` 绑定到 `AbstractModuleBeforeMiddleware` 后生效；签名/解密通用流程在父类，本类只覆写 `checkAuth()` / `checkReplay()`。
- HTTP 响应日志和响应加密由 common-core 输出切面统一处理。
- AutoController 请求方式按“方法级 `#[AllowMethods]` → 类级 `AutoController(defaultMethods)` → `config('app.default_allow_methods')` → 默认 `['POST', 'GET']`”解析；包含 `GET` 时自动补 `HEAD`，`OPTIONS` 预检由 common-core `InitMiddleware` 统一处理。

示例请求：

```bash
curl -X POST 'http://127.0.0.1:9504/test/test.do' \
  -H 'Content-Type: application/json' \
  -d '{"uid":1,"email":"test@example.com"}'
```

实际路径会受 `ROUTE_PREFIX`、`ROUTE_SUFFIX` 和 AutoController 路由规则影响。

## 组件与配置示例

组件示例：

- `app/Components/BaseComponent.php`: 项目组件基类，继承 common-core `BaseCoreComponent`，并注入 Redis。
- `app/Components/Test/TestComponent.php`: 展示本地缓存、Redis hash/set 缓存等常见组件用法。
- `app/Components/Test/Object/TestObject.php`: 展示组件返回对象/结构化数据的写法。

配置常量示例：

- `app/Conf/ApiRcodeConf.php`: 项目自定义返回码扩展点。
- `app/Conf/ApiRequestConf.php`: 项目侧请求上下文 key。
- `app/Conf/ClientConf.php`: 客户端系统、终端类型枚举。

这些文件只是业务项目的起始示例，不会被 common-core 强制依赖。实际项目可以保留命名风格，也可以按业务模块拆分。

## WebSocket 示例

相关目录：

- `app/WebSocket/Bind/DefaultWsBindStrategy.php`
- `app/WebSocket/Hook/AppWsHook.php`
- `app/WebSocket/Controller/Test/TestController.php`
- `app/WebSocket/Service/Test/TestService.php`
- `app/WebSocket/Components/WsAccountComponent.php`
- `app/WebSocket/Conf`
- `app/WebSocket/AsyncQueue/Process/DefaultQueueConsumer.php`

启用配置：

```dotenv
ENABLE_WS=true
WS_PORT=9505
WS_LOCAL_ENABLE=true
REDIS_HOST=localhost
REDIS_PORT=6379
```

注意：

- Redis 需要支持 `HEXPIRE`，即 Redis `7.4+` 或兼容实现。
- `server.mode` 必须是 `SWOOLE_BASE`。
- local 环境默认不启用 WS 常驻进程；本地联调请设置 `WS_LOCAL_ENABLE=true`。
- WS 握手入口在 `AppWsHook::onHandshake()`，示例从 query 中读取 `Client-Token`，业务项目应替换成真实登录态解析。
- `WsAccountComponent` 当前只保留账户缓存和 token 校验接入点，默认不会放行真实登录；业务项目必须接入自己的账号体系。

默认绑定策略：

- `bindDimensions()` 绑定 `account_id`。
- `addressableDimensions()` 返回 `['account_id']`，支持按 account_id 定向推送。
- `onlineCheckDimensions()` 返回 `['account_id']`，支持心跳级在线检查。
- `uniqueDimensions()` 继承默认空数组，即同一 account_id 允许多连接。

维度设计原则：

- `addressableDimensions()` 决定哪些维度可用于推送寻址。
- `onlineCheckDimensions()` 决定哪些维度会维护 presence 在线索引。
- `uniqueDimensions()` 决定哪些维度单连接并踢旧。
- `device_type=ios/android/h5` 这类低基数维度适合做分组推送，不适合做在线检查。
- `account_id` 这类高基数且单值连接数可控的维度适合做在线检查。

WS 路由：

```php
Router::addServer('ws', function () {
    Router::get('/', 'Dleno\CommonCore\Websocket\Server\WebSocketEnter');
});
```

WS 业务消息示例：

- `WsRequestConf` 定义 WS 请求上下文 key 和示例请求头。
- `WsServerConf` 只保留业务自定义 cmd 类型；服务器注册、绑定、队列 key 已收敛到 common-core。
- `WebSocket\Controller\Test\TestController` / `WebSocket\Service\Test\TestService` 展示 WS cmd 进入 Controller/Service 后的参数校验和统一响应。
- `WebSocket\AsyncQueue\Process\DefaultQueueConsumer` 是 WS 目录下的队列进程示例，默认 `isEnable()` 返回 false，不会随服务启动执行。

## 队列与进程

### AsyncQueue

配置：`config/autoload/async_queue.php`

示例（静态队列 + 动态队列两套，目录结构与 `common-core/examples/AsyncQueue` 一致）：

- `app/AsyncQueue/Job/TestJob.php`：Job 示例；`pushNormalExample()` 普通（立即）投递、`pushDelayExample()` 延时投递分别演示（延时即 `AsyncQueue::push($job, $delay)` 的第二参，单位秒）。
- `app/AsyncQueue/Process/TestAsyncQueueConsumer.php`：消费 `test` 队列的进程示例。
- `app/AsyncQueue/Dynamic/Job/DynamicJob.php` + `app/AsyncQueue/Dynamic/Process/DynamicQueueConsumer.php`：按服务器 IP 生成动态队列名（每台机消费自己的队列）的示例。

说明：

- `BaseQueueConsumer` / `BaseJob` 来自 common-core。
- Job 可指定 `$queue`，`AsyncQueue::push()` 会按队列名选择 driver；队列未在 `async_queue.php` 配置时，Job 的 `getConfig()` 复用 default 配置自动注册。
- redis 异步队列只有「普通 + 延时」两种投递（无死信能力，死信是 AMQP 的特性，见下）。
- 示例 Consumer 默认 `isEnable()` 返回 false，避免脚手架启动后拉起无意义的测试进程；业务启用前应明确队列名、并发和配置。

### AMQP

配置：`config/autoload/amqp.php`

示例（三种调用方式分别成对，目录结构与 `common-core/examples/Amqp` 一致）：

- 普通调用：`app/Amqp/Producer/NormalProducer.php` + `app/Amqp/Consumer/NormalConsumer.php`（直连交换机、立即投递）。
- 延时调用：`app/Amqp/Producer/DelayProducer.php` + `app/Amqp/Consumer/DelayConsumer.php`（`delayExchange=true`，x-delayed-message 插件方案，生产/消费须一致）。
- 延时到死信调用：`app/Amqp/Producer/DelayDlxProducer.php` → `app/Amqp/Consumer/DelayDlxBufferConsumer.php`（声明带 x-message-ttl + 死信的延时缓冲队列，**无活跃消费者**）→ 过期转投死信 → `app/Amqp/Consumer/DelayDlxDeadConsumer.php` 消费（不依赖延时插件的延时方案）。
- 动态路由：`app/Amqp/Producer/DcsTestProducer.php` + `app/Amqp/Consumer/DcsTestConsumer.php`（按服务器动态 routingKey / queue）。
- AMQP 示例 Consumer 保留 `AMQP_ENABLE` 前置门禁，但示例自身仍默认 `return false`，业务确认队列名、并发和消费逻辑后再改成自己的启用条件。

启用：

```dotenv
AMQP_ENABLE=true
AMQP_HOST=localhost
AMQP_PORT=5672
AMQP_USER=admin
AMQP_PASSWORD=admin
AMQP_VHOST=/
```

说明：

- `BaseProducer` / `BaseConsumer` 来自 common-core。
- 两种延时实现：① 延迟交换机（x-delayed-message 插件，见「延时调用」）；② 死信交换机 + 消息 TTL + 队列 TTL（TTL 到期转死信，见「延时到死信调用」，不需插件）。生产者与消费者的 `delayExchange` 必须一致；TTL 延时缓冲队列必须无活跃消费者。
- 延时插件方案需 RabbitMQ 安装 `rabbitmq_delayed_message_exchange` 插件；TTL+死信方案不需要插件。

### Crontab / Process

Crontab 配置：

```dotenv
ENABLE_CRONTAB=true
```

相关文件：

- `config/autoload/crontab.php`
- `config/autoload/processes.php`
- `app/TaskCron/TestCrontab.php`
- `app/Process/TestProcess.php`

Crontab 调度进程由 common-core `ConfigProvider` 按 `ENABLE_CRONTAB` 自动注册；`config/autoload/processes.php` 只用于追加业务自定义 Process，不要重复添加 Crontab 调度进程。

示例里的自定义 Process / Crontab 默认关闭；Crontab 示例保留 `ENABLE_CRONTAB` 前置门禁，业务使用时应先确认不会产生重复消费、重复任务或本地调试副作用。

## Model 与分表

相关文件：

- `app/Model/BaseModel.php`
- `app/Model/Test.php`
- `app/Model/TestSplit.php`

能力：

- `BaseModel` 继承 common-core 的模型基类。
- 支持普通模型查询。
- 支持按年/月/日/周/固定数量分表。
- 支持 `findById()`、`updateById()`、`deleteById()` 按主键路由分表。
- 支持 `withTable()` 指定分表后缀。

测试覆盖：

- `test/Cases/BaseModelSplitTableTest.php`

该测试会创建和删除测试表，运行前请确认连接的是本地测试数据库。

## 接口签名与加密

配置：`config/autoload/app.php`

关键配置：

- `API_CHECK_SIGN`: 是否开启接口签名。
- `API_DATA_CRYPT`: 是否开启接口加密。
- `SIGN_KEY`: 接口签名密钥。
- `SIGN_PREFIX`: 签名前缀。
- `SIGN_EXPIRE`: 签名时间偏移容忍秒数。
- `RSA_PRIVATE_KEY` / `RSA_PUBLIC_KEY`: 接口加密 RSA 密钥对。

注意：

- HTTP 响应加密由 common-core 输出切面处理，开关为 `API_DATA_CRYPT`。
- `Client-Key`（AES 密钥）的 RSA 解密使用 `OpenSslRsa2`（密文 base64，比 `OpenSslRsa` 的 hex 约短一半），客户端加密 `Client-Key` 须使用同款算法。
- 防重放默认不开启：包内 `DefaultModuleBeforeMiddleware::checkReplay()` 默认放行。如需拦截「同一已签名请求在 `SIGN_EXPIRE` 窗口内被原样重放」，在 `app/Middleware/AppModuleBeforeMiddleware::checkReplay()` 删除开头的 `return true;` 即启用示例实现（以整段签名 `Client-Sign` 为维度 `SET NX` 占位，命中即判定重放）；仅对非幂等接口有意义，会给签名请求增加一次 Redis 往返。
- 仓库中的 `.env` 仅适合作为开发模板参考，生产环境必须使用独立密钥。
- 不要把生产 `SIGN_KEY`、RSA 私钥、数据库密码、Redis 密码提交到仓库。
- `filter_headers` 会过滤访问日志中的敏感请求头，业务新增敏感头时应同步加入。

## 日志与 DingTalk 告警

日志配置入口：

- `config/autoload/logger.php`：使用 common-core 的默认日志分组和 handler。
- `config/autoload/dingtalk.php`：普通系统通知和异常追踪机器人配置，默认关闭。

异常输出会读取 `dingtalk.trace` 指定的机器人配置；未配置、未启用或 token/secret 为空时不会发送通知。生产启用前应准备独立机器人密钥，并确认 Redis 可用，因为 DingTalk 发送频率限制使用 Redis pool。

## 重要环境变量

基础：

- `APP_NAME`
- `APP_ENV`
- `IS_PROD`
- `APP_DEBUG`
- `HTTP_SCHEME`
- `SCAN_CACHEABLE`
- `DATE_DEFAULT_TIMEZONE`
- `LOG_MAX_FILES`
- `ENABLE_HTTP`
- `HTTP_PORT`
- `ENABLE_WS`
- `WS_PORT`
- `ENABLE_CRONTAB`

Swoole：

- `WORK_NUM`
- `MAX_COROUTINE`
- `MAX_REQUEST`
- `REACTOR_NUM`
- `SOCKET_BUFFER_SIZE`
- `BUFFER_OUTPUT_SIZE`
- `BACKLOG`
- `MAX_WAIT_TIME`
- `PACKAGE_MAX_LENGTH`
- `WS_PACKAGE_MAX_LENGTH`
- `WEBSOCKET_COMPRESSION`
- `WS_HEARTBEAT_CHECK_INTERVAL`
- `WS_HEARTBEAT_IDLE_TIME`

HTTP / API：

- `ROUTE_PREFIX`
- `ROUTE_SUFFIX`
- `ADMIN_MODULE_NAME`
- `HTTP_INIT_MIDDLEWARE_ENABLE`
- `API_DATA_CRYPT`
- `API_CHECK_SIGN`
- `SIGN_KEY`
- `SIGN_PREFIX`
- `SIGN_EXPIRE`
- `RSA_PRIVATE_KEY`
- `RSA_PUBLIC_KEY`

WebSocket：

- `WS_AUTH_MIDDLEWARE_ENABLE`
- `WS_LOCAL_ENABLE`
- `WS_KEY_PREFIX`
- `WS_SERVER_SET_CACHE_MS`
- `WS_PRESENCE_BUCKET_NUM`
- `WS_REALTIME_ONLINE_MAX`
- `WS_REALTIME_ONLINE_TIMEOUT`
- `WS_CONSUMER_PROCESSES`
- `WS_CONSUMER_LIMIT`
- `WS_CONSUMER_MAX_MESSAGES`
- `WS_DEDICATED_QUEUE_ENABLE`
- `WS_DEDICATED_PROCESSES`
- `WS_DEDICATED_LIMIT`
- `WS_DEDICATED_MAX_MESSAGES`

数据库 / Redis / AMQP：

- `DB_DRIVER`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `DB_CHARSET`
- `DB_COLLATION`
- `DB_PREFIX`
- `DB_MIN_CONNECTION`
- `DB_MAX_CONNECTION`
- `DB_CONNECT_TIMEOUT`
- `DB_WAIT_TIMEOUT`
- `DB_HEARTBEAT`
- `DB_MAX_IDLE_TIME`
- `DB_READ_HOST`
- `DB_WRITE_HOST`
- `DB_READ_MIN_CONNECTION`
- `DB_READ_MAX_CONNECTION`
- `DB_READ_CONNECT_TIMEOUT`
- `DB_READ_WAIT_TIMEOUT`
- `DB_READ_HEARTBEAT`
- `DB_READ_MAX_IDLE_TIME`
- `DB_WRITE_MIN_CONNECTION`
- `DB_WRITE_MAX_CONNECTION`
- `DB_WRITE_CONNECT_TIMEOUT`
- `DB_WRITE_WAIT_TIMEOUT`
- `DB_WRITE_HEARTBEAT`
- `DB_WRITE_MAX_IDLE_TIME`
- `REDIS_HOST`
- `REDIS_PORT`
- `REDIS_AUTH`
- `REDIS_USER`
- `REDIS_DB`
- `REDIS_MIN_CONNECTION`
- `REDIS_MAX_CONNECTION`
- `REDIS_CONNECT_TIMEOUT`
- `REDIS_WAIT_TIMEOUT`
- `REDIS_HEARTBEAT`
- `REDIS_MAX_IDLE_TIME`
- `AMQP_ENABLE`
- `AMQP_OPEN_SSL`
- `AMQP_HOST`
- `AMQP_PORT`
- `AMQP_USER`
- `AMQP_PASSWORD`
- `AMQP_VHOST`
- `AMQP_CONNECTION`
- `AMQP_MIN_CONNECTION`
- `AMQP_MAX_CONNECTION`
- `AMQP_CONNECT_TIMEOUT`
- `AMQP_WAIT_TIMEOUT`
- `AMQP_READ_WRITE_TIMEOUT`
- `AMQP_HEARTBEAT`
- `AMQP_MAX_IDLE_CHANNELS`

RPC / 服务发现：

- `CONSUL_SERVER_URI`
- `RPC_CONNECT_TIMEOUT`
- `RPC_RECV_TIMEOUT`
- `RPC_MIN_CONNECTION`
- `RPC_MAX_CONNECTION`
- `RPC_WAIT_TIMEOUT`
- `RPC_HEARTBEAT`
- `RPC_MAX_IDLE_TIME`

DingTalk：

- `DINGTALK_ROBOT_ENABLE`
- `DINGTALK_ROBOT_NAME`
- `DINGTALK_ROBOT_FREQUENCY`
- `DINGTALK_ROBOT_01_TOKEN`
- `DINGTALK_ROBOT_01_SECRET`
- `DINGTALK_ROBOT_02_TOKEN`
- `DINGTALK_ROBOT_02_SECRET`
- `DINGTALK_TRACE_ENABLE`
- `DINGTALK_TRACE_NAME`
- `DINGTALK_TRACE_FREQUENCY`
- `DINGTALK_TRACE_01_TOKEN`
- `DINGTALK_TRACE_01_SECRET`
- `DINGTALK_TRACE_02_TOKEN`
- `DINGTALK_TRACE_02_SECRET`

## 测试与静态分析

运行测试：

```bash
composer test
```

按用例过滤：

```bash
composer test -- --filter=ExampleTest
composer test -- --filter=DcsLockConcurrencyTest
composer test -- --filter=BaseModelSplitTableTest
```

静态分析：

```bash
composer analyse
```

代码格式化：

```bash
composer cs-fix
```

测试注意：

- `DcsLockConcurrencyTest` 需要可用 Redis，并且并发数不要超过 Redis 连接池容量。
- `BaseModelSplitTableTest` 需要本地测试 MySQL，会创建/删除 `shard_test*` 测试表。
- `ExampleTest` 只校验根路由返回 200 和空内容。

## Docker

项目包含：

- `Dockerfile`
- `docker-compose.yml`
- `.devcontainer/Dockerfile`

可按本地环境使用容器启动依赖。不同团队的本地 Docker 编排可能不同，使用前请确认 `.env.local` 中 MySQL / Redis / RabbitMQ 主机名和密码与容器一致。

## 与 common-core 的关系

`hyperf-diy` 负责“业务项目模板和接入示例”，`common-core` 负责“公共核心能力”。推荐边界：

- 可复用的框架能力、通用工具、WS 基础设施，应放入 `dleno/common-core`。
- 业务身份、业务维度、业务 Controller/Service/Model，应放在本项目或实际业务项目。
- 不建议在业务项目继承并改写 common-core 的 WS 基建进程和底层队列逻辑；应该通过配置、策略和 Hook 接入。

## 发布与升级 common-core

查看当前 common-core 版本：

```bash
composer show dleno/common-core
```

升级 common-core：

```bash
composer update dleno/common-core -W
```

升级后建议执行：

```bash
composer analyse
composer test
```

## 安全提示

- 不要提交生产 `.env`。
- 不要提交生产 RSA 私钥、签名密钥、数据库密码、Redis 密码、AMQP 密码。
- 开启 WS 前确认 Redis 版本和 `SWOOLE_BASE`。
- 开启 AMQP / AsyncQueue / Crontab / Process 前确认示例进程是否仍保持默认关闭，避免无意消费或重复任务。
- 低基数 WS 维度不要放入 `onlineCheckDimensions()`。
