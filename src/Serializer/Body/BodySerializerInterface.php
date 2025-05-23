<?php

namespace Verdient\Http\Serializer\Body;

use Verdient\Http\Serializer\SerializerInterface;

/**
 * 消息体序列化器接口
 *
 * @author Verdient。
 */
interface BodySerializerInterface extends SerializerInterface
{
    /**
     * 附加的头部
     *
     * @param mixed $data 待序列化的数据
     * @author Verdient。
     */
    public function headers(mixed $data): array;
}
