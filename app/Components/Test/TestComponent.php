<?php

declare(strict_types=1);

namespace App\Components\Test;

use App\Components\BaseComponent;
use App\Components\Test\Object\TestObject;
use App\Model\Test;
use Dleno\CommonCore\Exception\AppException;

/**
 * 封装测试组件.
 */
class TestComponent extends BaseComponent
{
    const CACHE_DATA_KEY = 'common:test:data:';

    const CACHE_LIST_KEY = 'common:test:list';

    private static $cacheTimeout = 30;//秒
    private static $cacheData    = [];
    private static $cacheTimes   = [];

    /**
     * 获取对应数据(可设置多层级本地缓存，通过下面的数组维度控制)
     * @param $key
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
     * 获取对应数据缓存
     * @param $key
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
     * 设置对应数据缓存
     * @param $key
     * @param $data
     * @return bool
     */
    public function setCacheData($key, $data)
    {
        $cacheKey = $this->getCacheDataKey($key);
        return $this->redis->hMset($cacheKey, $data);
    }

    /**
     * 获取数据缓存key
     * @param $key
     * @return string
     */
    private function getCacheDataKey($key)
    {
        return self::CACHE_DATA_KEY . $key;
    }

    /**
     * 获取应用列表缓存
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
     * 设置列表缓存
     * @param $list
     * @return bool
     */
    public function setCacheList($list)
    {
        $cacheKey = $this->getCacheListKey();
        return $this->redis->sAddArray($cacheKey, $list);
    }

    /**
     * 获取列表缓存key
     * @return string
     */
    private function getCacheListKey()
    {
        return self::CACHE_LIST_KEY;
    }

    /**
     * 封装测试组件方法1
     * @return int
     */
    public function test1(TestObject $testObj)
    {
        return 1;
    }

    /**
     * 封装测试组件方法2
     * @return bool
     */
    public function test2(TestObject $testObj)
    {
        return true;
    }


}
