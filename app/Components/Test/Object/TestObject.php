<?php

namespace App\Components\Test\Object;

use Dleno\CommonCore\Traits\ObjectAttribute;

class TestObject
{
    use ObjectAttribute;

    /**
     * id
     * @var int
     */
    private $id;

    /**
     * 属性1
     * @var int
     */
    private $attr1;

    /**
     * 属性2
     * @var string
     */
    private $attr2;

    /**
     * 属性3
     * @var string
     */
    private $attr3;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return self
     */
    public function setId($id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getAttr1()
    {
        return $this->attr1;
    }

    /**
     * @param int $attr1
     * @return self
     */
    public function setAttr1($attr1): self
    {
        $this->attr1 = $attr1;
        return $this;
    }

    /**
     * @return string
     */
    public function getAttr2()
    {
        return $this->attr2;
    }

    /**
     * @param string $attr2
     * @return self
     */
    public function setAttr2($attr2): self
    {
        $this->attr2 = $attr2;
        return $this;
    }

    /**
     * @return string
     */
    public function getAttr3()
    {
        return $this->attr3;
    }

    /**
     * @param string $attr3
     * @return self
     */
    public function setAttr3($attr3): self
    {
        $this->attr3 = $attr3;
        return $this;
    }
}