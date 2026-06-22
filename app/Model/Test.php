<?php

declare(strict_types=1);

namespace App\Model;

/**
 * 普通模型示例。
 *
 * 对应 `test` 表，展示 fillable、casts 和基础 getter/setter 的写法。
 *
 * @property int $id
 * @property string $key
 * @property string $attr1
 * @property int $attr2
 * @property string $attr3
 * @property string $create_time
 * @property string $update_time
 */
class Test extends BaseModel
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'test';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['id', 'key', 'attr1', 'attr2', 'attr3', 'create_time', 'update_time'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = ['id' => 'integer', 'attr2' => 'integer', 'attr3' => 'decimal:2'];

    /**
     * 获取主键 ID。
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * 设置主键 ID。
     *
     * @param int $id
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * 获取业务 key。
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * 设置业务 key。
     *
     * @param string $key
     * @return self
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * 获取属性 attr1。
     */
    public function getAttr1()
    {
        return $this->attr1;
    }

    /**
     * 设置属性 attr1。
     *
     * @param string $attr1
     * @return self
     */
    public function setAttr1($attr1)
    {
        $this->attr1 = $attr1;
        return $this;
    }

    /**
     * 获取属性 attr2。
     */
    public function getAttr2()
    {
        return $this->attr2;
    }

    /**
     * 设置属性 attr2。
     *
     * @param int|string $attr2
     * @return self
     */
    public function setAttr2($attr2)
    {
        $this->attr2 = $attr2;
        return $this;
    }

    /**
     * 获取属性 attr3。
     */
    public function getAttr3()
    {
        return $this->attr3;
    }

    /**
     * 设置属性 attr3。
     *
     * @param string $attr3
     * @return self
     */
    public function setAttr3($attr3)
    {
        $this->attr3 = $attr3;
        return $this;
    }

    /**
     * 获取创建时间。
     */
    public function getCreateTime()
    {
        return $this->create_time;
    }

    /**
     * 设置创建时间。
     *
     * @param string $create_time
     * @return self
     */
    public function setCreateTime($create_time)
    {
        $this->create_time = $create_time;
        return $this;
    }

    /**
     * 获取更新时间。
     */
    public function getUpdateTime()
    {
        return $this->update_time;
    }

    /**
     * 设置更新时间。
     *
     * @param string $update_time
     * @return self
     */
    public function setUpdateTime($update_time)
    {
        $this->update_time = $update_time;
        return $this;
    }
}
