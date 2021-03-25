<?php

declare(strict_types=1);

namespace Verdient\http\api;

use chorus\InvalidParamException;

/**
 * 格式化器
 * @author Verdient。
 */
class Formatter
{
    /**
     * @var string 整型
     * @author Verdient。
     */
    const INT = 'int';

    /**
     * @var string 浮点型
     * @author Verdient。
     */
    const FLOAT = 'float';

    /**
     * @var string 双精度浮点型
     * @author Verdient。
     */
    const DOUBLE = 'double';

   /**
     * @var string 字符串
     * @author Verdient。
     */
    const STRING = 'string';

   /**
     * @var string 数组
     * @author Verdient。
     */
    const ARRAY = 'array';

   /**
     * @var string 布尔
     * @author Verdient。
     */
    const BOOLEAN = 'boolean';

    /**
     * @var string 任意类型
     * @author Verdient。
     */
    const ANY = 'any';

    /**
     * 格式化
     * @var string 类型
     * @var mixed $value 值
     * @author Verdient。
     */
    public static function format($type, $value){
        switch($type){
            case static::INT:
                $value = (int) $value;
                break;
            case static::FLOAT:
                $value = (float) $value;
                break;
            case static::DOUBLE:
                $value = (double) $value;
                break;
            case static::STRING:
                $value = (string) $value;
                break;
            case static::ARRAY:
                $value = (array) $value;
                break;
            case static::BOOLEAN:
                $value = (bool) $value;
                break;
            case static::ANY:
                break;
            default:
                throw new InvalidParamException('Unknown format type: ' . $type);
                break;
        }
        return $value;
    }
}