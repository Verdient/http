<?php

namespace Verdient\http\serializer\body;

/**
 * Urlencoded消息体序列化器
 * @author Verdient。
 */
class UrlencodedBodySerializer implements BodySerializerInterface
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public static function serialize($data): string
    {
        return http_build_query($data);
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public static function headers($data): array
    {
        return [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
    }
}
