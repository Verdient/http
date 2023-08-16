<?php

namespace Verdient\http\serializer\query;

use Verdient\http\serializer\SerializerInterface;

/**
 * 保持原样序列化器
 * @author Verdient。
 */
class OriginalSerializer implements SerializerInterface
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public static function serialize($data): string
    {
        return static::normalize($data);
    }

    /**
     * 格式化
     * @param array $data 待格式化的数据
     * @param string[] $prefixs 前缀集合
     * @return string
     * @author Verdient。
     */
    protected static function normalize(array $data, $prefixs = []): string
    {
        $isIndexed = static::isIndexed($data);
        $results = [];
        foreach ($data as $name => $value) {
            $name2 = $isIndexed ? '' : $name;
            if (is_array($value)) {
                if (empty($prefixs)) {
                    $prefixs[] = $name;
                } else {
                    $prefixs[] = '[' . $name2 . ']';
                }
                $results[] = static::normalize($value, $prefixs);
            } else {
                if (empty($prefixs)) {
                    $results[] = $name2 . '=' . $value;
                }
                $results[] .= implode('', $prefixs) . '[' . $name2 . ']'  . '=' . $value;
            }
        }
        return implode('&', $results);
    }

    /**
     * 判断数组是否是索引数组
     * @param array $array 数组
     * @return bool
     * @author Verdient。
     */
    protected static function isIndexed(array $array)
    {
        $keys = array_keys($array);
        return array_keys($keys) === $keys;
    }
}
