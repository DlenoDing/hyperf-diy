<?php

declare(strict_types=1);

namespace App\Components\Test;

use App\Components\BaseComponent;
use App\Components\Test\Object\TestObject;
use App\Model\Test;
use Dleno\CommonCore\Exception\AppException;

/**
 * 测试组件示例。
 *
 * 展示本地进程缓存、Redis hash 缓存、Redis set 列表缓存和结构化对象返回。
 */
class TestComponent extends BaseComponent
{
    /**
     * 单条测试数据 Redis hash 缓存前缀。
     */
    const CACHE_DATA_KEY = 'common:test:data:';

    /**
     * 测试数据 key 列表 Redis set 缓存 key。
     */
    const CACHE_LIST_KEY = 'common:test:list';

    /**
     * 本地进程缓存有效期，单位秒。
     */
    private static $cacheTimeout = 30;//秒

    /**
     * 本地进程缓存数据。
     */
    private static $cacheData    = [];

    /**
     * 本地进程缓存过期时间戳。
     */
    private static $cacheTimes   = [];

    /**
     * 获取对应数据，优先使用本地进程缓存。
     *
     * @param string $key 业务 key
     * @return TestObject
     */
    public function getData($key)
    {
        //验证本地缓存过期
        if (isset(self::$cacheData[$key])) {
            if (self::$cacheTimes[$key] <= time()) {
                unset(self::$cacheData[$key], self::$cacheTimes[$key]);
            }
        }
        //无缓存数据，则重新缓存
        if (!isset(self::$cacheData[$key])) {
            self::$cacheData[$key]  = $this->getCacheData($key);
            self::$cacheTimes[$key] = time() + self::$cacheTimeout;
        }

        //clone 返回，避免外部使用时set对应值，造成数据混乱
        return (clone self::$cacheData[$key]);
    }

    /**
     * 从 Redis/数据库获取对应数据缓存。
     *
     * @param string $key 业务 key
     * @return TestObject
     */
    public function getCacheData($key)
    {
        $cacheKey = $this->getCacheDataKey($key);
        $data     = $this->redis->hGetAll($cacheKey);
        $data     = $data ?: [];
        if (empty($data)) {
            $data = Test::query()
                        ->where('key', $key)
                        ->first();
            $data = $data ? $data->toArray() : [];
            if (!empty($data)) {
                $this->setCacheData($key, $data);
            }
        }

        return (new TestObject())->fill($data);
    }

    /**
     * 写入单条数据 Redis hash 缓存。
     *
     * @param string $key 业务 key
     * @param array $data 缓存数据
     * @return bool
     */
    public function setCacheData($key, $data)
    {
        $cacheKey = $this->getCacheDataKey($key);
        return $this->redis->hMset($cacheKey, $data);
    }

    /**
     * 获取单条数据缓存 key。
     *
     * @param string $key 业务 key
     * @return string
     */
    private function getCacheDataKey($key)
    {
        return self::CACHE_DATA_KEY . $key;
    }

    /**
     * 获取测试数据 key 列表缓存。
     *
     * @return array
     */
    public function getCacheList()
    {
        $cacheKey = $this->getCacheListKey();
        $list     = $this->redis->sMembers($cacheKey);
        $list     = $list ?: [];
        if (empty($list)) {
            $list = Test::query()
                        ->get();
            $list = $list->isNotEmpty() ? $list->toArray() : [];
            //列表缓存
            $list = array_column($list, 'key');
            $this->setCacheList($list);
        }

        return $list;
    }

    /**
     * 写入测试数据 key 列表缓存。
     *
     * @param array $list key 列表
     * @return bool
     */
    public function setCacheList($list)
    {
        $cacheKey = $this->getCacheListKey();
        return $this->redis->sAddArray($cacheKey, $list);
    }

    /**
     * 获取测试数据 key 列表缓存 key。
     *
     * @return string
     */
    private function getCacheListKey()
    {
        return self::CACHE_LIST_KEY;
    }

    /**
     * 示例组件方法 1。
     *
     * @param TestObject $testObj 测试数据对象
     * @return int
     */
    public function test1(TestObject $testObj)
    {
        return 1;
    }

    /**
     * 示例组件方法 2。
     *
     * @param TestObject $testObj 测试数据对象
     * @return bool
     */
    public function test2(TestObject $testObj)
    {
        return true;
    }


}
