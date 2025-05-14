<?php

namespace Verdient\Http\Serializer;

/**
 * 序列化器接口
 *
 * @author Verdient。
 */
interface SerializerInterface
{
    /**
     * 序列化
     *
     * @param mixed $data 待序列化的数据
     * @author Verdient。
     */
    public function serialize(mixed $data): string;
}
