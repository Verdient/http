<?php

namespace Verdient\http\serializer\body;

use Verdient\http\serializer\SerializerInterface;

/**
 * 消息体序列化器接口
 * @author Verdient。
 */
interface BodySerializerInterface extends SerializerInterface
{
    /**
     * 附加的头部
     * @param mixed $data 待序列化的数据
     * @return array
     * @author Verdient。
     */
    public static function headers($data): array;
}
