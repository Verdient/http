<?php

namespace Verdient\Http\Serializer\Body;

/**
 * Urlencoded消息体序列化器
 *
 * @author Verdient。
 */
class UrlencodedBodySerializer implements BodySerializerInterface
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function serialize(mixed $data): string
    {
        if (empty($data)) {
            return '';
        }

        return http_build_query($data);
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function headers(mixed $data): array
    {
        return [
            'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'
        ];
    }
}
