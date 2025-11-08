<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\BaseModel;

/**
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
