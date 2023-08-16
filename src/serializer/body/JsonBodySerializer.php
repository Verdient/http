<?php

namespace Verdient\http\serializer\body;

/**
 * JSON消息体序列化器
 * @author Verdient。
 */
class JsonBodySerializer implements BodySerializerInterface
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public static function serialize($data): string
    {
        return json_encode($data);
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public static function headers($data): array
    {
        return [
            'Content-Type' => 'application/json'
        ];
    }
}
