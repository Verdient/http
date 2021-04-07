<?php

declare(strict_types=1);

namespace Verdient\http\api;

/**
 * 抽象请求
 * @author Verdient。
 */
abstract class AbstractRequest
{
    use RuleTrait;

    /**
     * @var array 数据
     * @author Verdient。
     */
    protected $data = [];

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function __construct($data = []){
        $this->attributes = $this->attributes();
        $this->data = $data;
    }

    /**
     * 转为数组
     * @return array
     * @author Verdient。
     */
    public function toArray(){
        if(!empty($this->attributes)){
            return $this->format($this->data) ?: [];
        }
        return [];
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function __set($name, $value){
        $this->data[$name] = $value;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function __isset($name){
        return isset($this->data[$name]);
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function __unset($name){
        if(isset($this->data[$name])){
            unset($this->data[$name]);
        }
    }

    /**
     * 属性配置
     * @return array
     * @author Verdinet。
     */
    abstract protected function attributes(): array;
}