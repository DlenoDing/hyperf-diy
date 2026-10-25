<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Cases;

use Composer\InstalledVersions;
use Dleno\CommonCore\Base\Amqp\ConnectionFactory;
use Dleno\CommonCore\Base\AsyncQueue\BaseQueueConsumer;
use Dleno\CommonCore\Process\CrontabDispatcherProcess;
use Dleno\CommonCore\Process\CrontabExecutor;
use Dleno\CommonCore\Signal\ProcessStopHandler;
use Hyperf\Amqp\Consumer;
use Hyperf\Amqp\Params;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Crontab\Strategy\Executor;
use Hyperf\Framework\Bootstrap\WorkerExitCallback;
use Hyperf\Server\Event;
use HyperfTest\TestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversNothing;
use ReflectionClass;
use RuntimeException;

/**
 * Verify the application's real provider/config wiring without contacting RabbitMQ.
 * @internal
 */
#[CoversNothing]
class CommonCoreIntegrationTest extends TestCase
{
    public function testPublishedPackageIsInstalledNormally(): void
    {
        $version = ltrim((string) InstalledVersions::getPrettyVersion('dleno/common-core'), 'v');
        $this->assertTrue(version_compare($version, '3.2.5', '>='));
        $path = InstalledVersions::getInstallPath('dleno/common-core');
        $this->assertNotNull($path);
        $this->assertFalse(is_link($path));
        $this->assertSame(realpath(BASE_PATH . '/vendor/dleno/common-core'), realpath($path));
        $this->assertStringStartsWith(realpath($path) . '/src/', (new ReflectionClass(ConnectionFactory::class))->getFileName());
        foreach ([BaseQueueConsumer::class, CrontabDispatcherProcess::class] as $class) {
            $this->assertSame(1.0, (new ReflectionClass($class))->getDefaultProperties()['recvTimeout']);
        }
    }

    public function testApplicationUsesPackageShutdownBindings(): void
    {
        $container = ApplicationContext::getContainer();
        foreach ([
            \Hyperf\Amqp\ConnectionFactory::class => ConnectionFactory::class,
            Consumer::class => \Dleno\CommonCore\Base\Amqp\Consumer::class,
            \Hyperf\Crontab\Process\CrontabDispatcherProcess::class => CrontabDispatcherProcess::class,
            Executor::class => CrontabExecutor::class,
            WorkerExitCallback::class => \Dleno\CommonCore\Process\WorkerExitCallback::class,
        ] as $key => $expected) {
            $this->assertInstanceOf($expected, $container->get($key), $key);
        }

        $config = $container->get(ConfigInterface::class);
        $processes = $config->get('processes', []);
        $count = count(array_filter($processes, static fn ($class) => $class === \Hyperf\Crontab\Process\CrontabDispatcherProcess::class));
        $this->assertSame($config->get('crontab.enable') ? 1 : 0, $count);
        $this->assertNotContains(CrontabDispatcherProcess::class, $processes);
        $this->assertSame(1, count(array_filter($config->get('signal.handlers', []), static fn ($class) => $class === ProcessStopHandler::class)));
        $this->assertSame([[2, SIGTERM], [2, SIGINT]], $container->get(ProcessStopHandler::class)->listen());
        $this->assertFalse(is_array($config->get('signal.timeout')));
        $this->assertSame([WorkerExitCallback::class, 'onWorkerExit'], $config->get('server.callbacks')[Event::ON_WORKER_EXIT]);
    }

    public function testHeartbeatDefaultsAndInvalidConfigBeforeNetwork(): void
    {
        $factory = ApplicationContext::getContainer()->get(\Hyperf\Amqp\ConnectionFactory::class);
        $called = false;
        try {
            $factory->make(['io' => function (array $config, Params $params) use (&$called): never {
                $called = true;
                $this->assertSame(15, $params->getHeartbeat());
                $this->assertSame(30, $params->getReadWriteTimeout());
                throw new HeartbeatIoChecked();
            }]);
            $this->fail('IO interception was bypassed');
        } catch (HeartbeatIoChecked) {
            $this->assertTrue($called);
        }

        $called = false;
        try {
            $factory->make(['params' => ['heartbeat' => 0], 'io' => function () use (&$called): never {
                $called = true;
                throw new RuntimeException('Invalid configuration reached IO');
            }]);
            $this->fail('Zero heartbeat was accepted');
        } catch (InvalidArgumentException $error) {
            $this->assertStringContainsString('heartbeat', $error->getMessage());
            $this->assertFalse($called);
        }
    }
}

class HeartbeatIoChecked extends RuntimeException
{
}
