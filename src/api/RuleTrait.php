<?php

declare(strict_types=1);

namespace Verdient\http\api;

/**
 * 规则特性
 * @author Verdient。
 */
trait RuleTrait
{
    /**
     * @var array 属性
     * @author Verdient。
     */
    protected $attributes = [];

    /**
     * 格式化响应
     * @param array $data 数据
     * @param string $rule 规则
     * @return array|null
     * @author Verdient。
     */
    public function format($data, $rule = ''){
        if(is_array($data)){
            $result = [];
            $isOK = false;
            foreach($data as $name => $value){
                foreach([(string) $name, '*'] as $ruleEnd){
                    $ruleName = $rule === '' ? $ruleEnd : ($rule . '.' . $ruleEnd);
                    if(is_array($value) && $this->hasSubRule($ruleName)){
                        $subResult = $this->format($value, $ruleName);
                        if(is_array($subResult)){
                            $isOK = true;
                            $result[$name] = $subResult;
                            break;
                        }
                    }else if($this->hasRule($ruleName)){
                        $isOK = true;
                        $result[$name] = Formatter::format($this->getRule($ruleName), $value);
                        break;
                    }
                }
            }
            return $isOK ? $result : null;
        }
        return $data;
    }

    /**
     * 判断是否有子规则
     * @param string $name 规则名称
     * @return bool
     * @author Verdient。
     */
    public function hasSubRule($name){
        if($name === ''){
            return !empty($this->attributes);
        }
        foreach($this->attributes as $key => $v){
            if(strpos($key, $name . '.') === 0){
                return true;
            }
        }
        return false;
    }

    /**
     * 判断是否有规则
     * @param string $name 规则名称
     * @return bool
     * @author Verdient。
     */
    public function hasRule($name){
        return isset($this->attributes[$name]);
    }

    /**
     * 获取规则
     * @param string $name 规则名称
     * @return string
     * @author Verdient。
     */
    public function getRule($name){
        return $this->attributes[$name] ?? null;
    }
}