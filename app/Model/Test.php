<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id
 * @property string $key key
 * @property int $attr1 属性1
 * @property string $attr2 属性2
 * @property string $attr3 属性3
 * @property string $create_time 
 * @property string $update_time 
 */
class Test extends BaseModel
{
    protected $primaryKey = 'app_id';
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'test';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'key', 'attr1', 'attr2', 'attr3', 'create_time', 'update_time'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'attr1' => 'integer'];

    public function getId()
    {
        return $this->id;
    }
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    public function getKey()
    {
        return $this->key;
    }
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }
    public function getAttr1()
    {
        return $this->attr1;
    }
    public function setAttr1($attr1)
    {
        $this->attr1 = $attr1;
        return $this;
    }
    public function getAttr2()
    {
        return $this->attr2;
    }
    public function setAttr2($attr2)
    {
        $this->attr2 = $attr2;
        return $this;
    }
    public function getAttr3()
    {
        return $this->attr3;
    }
    public function setAttr3($attr3)
    {
        $this->attr3 = $attr3;
        return $this;
    }
    public function getCreateTime()
    {
        return $this->create_time;
    }
    public function setCreateTime($create_time)
    {
        $this->create_time = $create_time;
        return $this;
    }
    public function getUpdateTime()
    {
        return $this->update_time;
    }
    public function setUpdateTime($update_time)
    {
        $this->update_time = $update_time;
        return $this;
    }
}