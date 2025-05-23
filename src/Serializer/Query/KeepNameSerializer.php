<?php

namespace Verdient\Http\Serializer\Query;

use Verdient\Http\Serializer\SerializerInterface;

/**
 * 保持键名序列化器
 *
 * @author Verdient。
 */
class KeepNameSerializer implements SerializerInterface
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function serialize(mixed $data): string
    {
        return $this->normalize($data);
    }

    /**
     * 格式化
     *
     * @param array $data 待格式化的数据
     * @param string[] $prefixs 前缀集合
     * @author Verdient。
     */
    protected function normalize(array $data, $prefixs = []): string
    {
        $isIndexed = $this->isIndexed($data);
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
                    $results[] = $name2 . '=' . urlencode($value);
                } else {
                    $results[] .= implode('', $prefixs) . '[' . $name2 . ']' . '=' . urlencode($value);
                }
            }
        }
        return implode('&', $results);
    }

    /**
     * 判断数组是否是索引数组
     *
     * @param array $array 数组
     * @author Verdient。
     */
    protected function isIndexed(array $array): bool
    {
        return array_is_list($array);
    }
}
