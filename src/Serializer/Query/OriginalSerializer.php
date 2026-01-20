<?php

namespace Verdient\Http\Serializer\Query;

use Override;
use Verdient\Http\Serializer\SerializerInterface;

/**
 * 保持原样序列化器
 *
 * @author Verdient。
 */
class OriginalSerializer implements SerializerInterface
{
    /**
     * @author Verdient。
     */
    #[Override]
    public function serialize(mixed $data): string
    {
        return $this->normalize($data);
    }

    /**
     * 格式化
     *
     * @param array $data 待格式化的数据
     * @param string[] $prefixs 前缀集合
     *
     * @author Verdient。
     */
    #[Override]
    protected function normalize(array $data, $prefixs = []): string
    {
        $isIndexed = array_is_list($data);
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
                } else {
                    $results[] .= implode('', $prefixs) . '[' . $name2 . ']' . '=' . $value;
                }
            }
        }
        return implode('&', $results);
    }
}
