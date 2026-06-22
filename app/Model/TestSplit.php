<?php

declare (strict_types=1);
namespace App\Model;

/**
 * 分表模型示例。
 *
 * 对应 `test_split` 基表，默认按月分表，主键使用 `app_id`。
 *
 * @property int $id
 * @property string $key key
 * @property int $attr1 属性1
 * @property string $attr2 属性2
 * @property string $attr3 属性3
 * @property string $create_time
 * @property string $update_time
 */
class TestSplit extends BaseModel
{
    /**
     * 自动分表模式，值决定分表的方式。
     */
    protected $splitMode = self::SPLIT_MODE_MONTH;

    /**
     * 分表路由主键示例。
     */
    protected string $primaryKey = 'app_id';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected ?string $table = 'test_split';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected array $fillable = ['id', 'key', 'attr1', 'attr2', 'attr3', 'create_time', 'update_time'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'attr1' => 'integer'];

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
     * @param int $attr1
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
     * @param string $attr2
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
