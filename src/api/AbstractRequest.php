<?php

declare(strict_types=1);

namespace Verdient\http\api;

use chorus\BaseObject;

/**
 * 抽象请求
 * @author Verdient。
 */
abstract class AbstractRequest extends BaseObject
{
    /**
     * @var array 属性
     * @author Verdient。
     */
    protected $attributes = [];

    /**
     * @var array 数据
     * @author Verdient。
     */
    protected $data = [];

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function __construct($config = []){
        $this->attributes = $this->attributes();
        parent::__construct($config);
    }

    /**
     * 转为数组
     * @return array
     * @author Verdient。
     */
    public function toArray(){
        return array_map(function($value){
            return is_bool($value) ? ($value ? 1 : 0) : $value;
        }, $this->data);
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function __set($name, $value){
        if(isset($this->attributes[$name])){
            $this->data[$name] = Formatter::format($this->attributes[$name], $value);
        }else{
            parent::__set($name, $value);
        }
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