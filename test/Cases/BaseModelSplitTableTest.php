<?php

declare(strict_types=1);

namespace HyperfTest\Cases;

use Dleno\CommonCore\Model\BaseModel;
use Hyperf\Coroutine\Coroutine;
use Hyperf\DbConnection\Db;
use Hyperf\Testing\TestCase;
use Swoole\Coroutine\WaitGroup;

use function Hyperf\Config\config;

/**
 * 数量分表测试模型
 */
class ShardNumModel extends BaseModel
{
    protected ?string $table = 'shard_test';
    protected $baseTable      = 'shard_test';
    protected $splitMode      = self::SPLIT_MODE_NUM;
    protected $splitMaxNum    = 1000;
    public $isAlias           = false;
    public bool $timestamps   = false;
    protected array $guarded  = [];
}

/**
 * 时间(按日)分表测试模型
 */
class ShardTimeModel extends BaseModel
{
    protected ?string $table = 'shard_test_t';
    protected $baseTable      = 'shard_test_t';
    protected $splitMode      = self::SPLIT_MODE_DAY;
    public $isAlias           = false;
    public bool $timestamps   = false;
    protected array $guarded  = [];
}

/**
 * BaseModel 分表（建表竞态修复）完整测试
 *
 * 运行：composer test -- --filter=BaseModelSplitTableTest
 * 依赖本地 mysql 容器的 test 库（由 .env.local 指向）。
 *
 * 覆盖场景：
 *  1. 数量分表：建表即原子写入正确 AUTO_INCREMENT（无 CREATE/ALTER 空窗）
 *  2. 数量分表 shard0：默认自增基值=1
 *  3. 时间分表：新分表自增值延续上一分表
 *  4. 并发抢建同一分表：无异常、表只建一次、自增基值正确
 *  5. 表已存在：直接复用、不报错、不覆盖既有表
 *  6. 非法表名：白名单拦截，杜绝 DDL 注入，不建任何表
 *  7. isTableExistsError：精确识别 1050 / "already exists"
 *
 * @internal
 * @coversNothing
 */
class BaseModelSplitTableTest extends TestCase
{
    private string $database;

    /**
     * 每个用例前重建基表和清空 BaseModel 静态缓存，保证测试互相隔离。
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->database = (string) config('databases.default.database');

        //让 information_schema 的 AUTO_INCREMENT 实时反映（关闭统计缓存），保证断言确定性
        try {
            Db::statement('SET GLOBAL information_schema_stats_expiry = 0');
        } catch (\Throwable $e) {
            //无权限则忽略，多数断言走 insertGetId 不依赖此项
        }

        $this->resetStaticCache();
        $this->cleanTables();
        $this->createBaseTable('shard_test');
        $this->createBaseTable('shard_test_t');
    }

    /**
     * 每个用例后清理测试表，避免污染本地测试数据库。
     */
    protected function tearDown(): void
    {
        $this->cleanTables();
        parent::tearDown();
    }

    /**
     * 场景1：数量分表，建表即带正确 AUTO_INCREMENT。
     * 表序号 1 基：shard 00002 承载 id [splitMaxNum*1+1, splitMaxNum*2]，自增基值 = splitMaxNum*(2-1)+1 = 1001，
     * 首条插入 id 必须正好是 1001，证明表"出生"就带正确自增基值（与 ceil(id/splitMaxNum) 反查一致），不存在 CREATE→ALTER 空窗。
     */
    public function testNumShardAtomicAutoIncrement(): void
    {
        $id = ShardNumModel::withTable('00002')->insertGetId(['name' => 'a']);
        $this->assertSame(1001, (int) $id, 'shard 00002 首条 id 应为 1001（1 基自增基值）');
        $this->assertTrue($this->tableExists('shard_test@00002'));
    }

    /**
     * 场景2：数量分表 shard 00000，自增基值为默认 1。
     */
    public function testNumShardZeroDefaultAutoIncrement(): void
    {
        $id = ShardNumModel::withTable('00000')->insertGetId(['name' => 'a']);
        $this->assertSame(1, (int) $id, 'shard 00000 首条 id 应为 1');
    }

    /**
     * 场景3：时间分表，新分表自增值延续上一分表。
     * 预置 2026-06-18 分表自增=500，则新建 2026-06-19 分表应从 500 开始。
     */
    public function testTimeShardAutoIncrementContinues(): void
    {
        //预置上一分表，born 自增=500
        Db::statement('CREATE TABLE `shard_test_t@2026-06-18` LIKE `shard_test_t`');
        Db::statement('ALTER TABLE `shard_test_t@2026-06-18` AUTO_INCREMENT=500');
        $this->resetStaticCache();

        $id = ShardTimeModel::withTable('2026-06-19')->insertGetId(['name' => 'a']);
        $this->assertSame(500, (int) $id, '新时间分表应延续上一分表自增值 500');
    }

    /**
     * 场景4：高并发抢建同一分表。
     * 多协程同时触发同一新分表创建：不得抛异常，表只被建一次，自增基值仍正确。
     */
    public function testConcurrentCreateSameShard(): void
    {
        $n          = 30;
        $errors     = [];
        $wg         = new WaitGroup();
        for ($i = 0; $i < $n; $i++) {
            $wg->add();
            Coroutine::create(function () use (&$errors, $wg) {
                try {
                    //仅触发建表（构造器内完成），不插入
                    ShardNumModel::withTable('00005');
                } catch (\Throwable $e) {
                    $errors[] = get_class($e) . ': ' . $e->getMessage();
                } finally {
                    $wg->done();
                }
            });
        }
        $wg->wait(30);

        $this->assertSame([], $errors, '并发建表不应抛出异常');
        $this->assertTrue($this->tableExists('shard_test@00005'), '并发后分表应已创建');

        //自增基值仍为 splitMaxNum*(5-1)+1 = 4001（1 基）
        $this->resetStaticCache();
        $id = ShardNumModel::withTable('00005')->insertGetId(['name' => 'a']);
        $this->assertSame(4001, (int) $id, '并发建表后自增基值应正确（4001）');
    }

    /**
     * 场景5：分表已存在。
     * 预置 shard 00003（自增=9999），再触发建表：不报错、不覆盖，沿用既有自增值。
     */
    public function testExistingShardReused(): void
    {
        Db::statement('CREATE TABLE `shard_test@00003` LIKE `shard_test`');
        Db::statement('ALTER TABLE `shard_test@00003` AUTO_INCREMENT=9999');
        $this->resetStaticCache();

        $id = ShardNumModel::withTable('00003')->insertGetId(['name' => 'a']);
        $this->assertSame(9999, (int) $id, '已存在分表应被复用，不被重建/覆盖');
    }

    /**
     * 场景6：非法分表名（含反引号/空格）被白名单拦截，杜绝 DDL 注入，且不建任何表。
     */
    public function testInvalidTableNameRejected(): void
    {
        $malicious = '00006`;DROP TABLE `shard_test';
        //触发建表流程：splitCheckInitTable 应直接返回 false，不抛异常、不执行 DDL
        ShardNumModel::withTable($malicious);

        //基表必须完好无损（未被注入语句 DROP）
        $this->assertTrue($this->tableExists('shard_test'), '基表不应被注入语句破坏');
        //不应创建出任何带该恶意片段的分表
        $count = Db::select(
            'SELECT COUNT(*) AS c FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME LIKE ?',
            [$this->database, 'shard_test@00006%']
        );
        $this->assertSame(0, (int) ($count[0]['c'] ?? -1), '非法表名不应创建任何分表');
    }

    /**
     * 场景7：isTableExistsError 精确识别。
     */
    public function testIsTableExistsErrorDetection(): void
    {
        $ref    = new \ReflectionClass(BaseModel::class);
        $model  = $ref->newInstanceWithoutConstructor();
        $method = $ref->getMethod('isTableExistsError');
        $method->setAccessible(true);

        //1050 错误码
        $pdo            = new \PDOException('SQLSTATE[42S01]: Base table or view already exists: 1050');
        $pdo->errorInfo = ['42S01', 1050, 'Table already exists'];
        $this->assertTrue($method->invoke($model, $pdo), '应识别 1050 为表已存在');

        //仅消息含 already exists
        $msgEx = new \Exception("Table 'x' already exists");
        $this->assertTrue($method->invoke($model, $msgEx), '应识别消息 already exists');

        //其它错误
        $other = new \RuntimeException('Connection refused');
        $this->assertFalse($method->invoke($model, $other), '其它错误不应被当作表已存在');
    }

    //========================= 辅助方法 =========================

    /**
     * 清理 BaseModel 的静态表名缓存，避免不同测试用例互相污染。
     */
    private function resetStaticCache(): void
    {
        $ref = new \ReflectionClass(BaseModel::class);
        foreach (['hasTable', 'currTable'] as $prop) {
            $p = $ref->getProperty($prop);
            $p->setAccessible(true);
            $p->setValue(null, []);
        }
    }

    /**
     * 创建分表测试基表。
     */
    private function createBaseTable(string $name): void
    {
        Db::statement('DROP TABLE IF EXISTS `' . $name . '`');
        Db::statement(
            'CREATE TABLE `' . $name . '` ('
            . '`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, '
            . '`name` VARCHAR(50) NULL DEFAULT NULL, '
            . 'PRIMARY KEY (`id`)'
            . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    /**
     * 清理测试过程中创建的基表和分表。
     */
    private function cleanTables(): void
    {
        $rows = Db::select(
            'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? '
            . "AND (TABLE_NAME LIKE 'shard_test@%' OR TABLE_NAME LIKE 'shard_test_t@%' "
            . "OR TABLE_NAME = 'shard_test' OR TABLE_NAME = 'shard_test_t')",
            [$this->database]
        );
        foreach ($rows as $row) {
            $name = $row['TABLE_NAME'] ?? null;
            if ($name !== null) {
                Db::statement('DROP TABLE IF EXISTS `' . $name . '`');
            }
        }
    }

    /**
     * 判断指定测试表是否存在。
     */
    private function tableExists(string $name): bool
    {
        $rows = Db::select(
            'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
            [$this->database, $name]
        );
        return count($rows) > 0;
    }
}
