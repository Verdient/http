<?php

namespace Verdient\http\serializer;

/**
 * 序列化器接口
 * @author Verdient。
 */
interface SerializerInterface
{
    /**
     * 序列化
     * @param mixed $data 待序列化的数据
     * @return string
     * @author Verdient。
     */
    public static function serialize($data): string;
}
