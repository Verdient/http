<?php

namespace Verdient\Http\Serializer\Body;

/**
 * JSON消息体序列化器
 *
 * @author Verdient。
 */
class JsonBodySerializer implements BodySerializerInterface
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function serialize(mixed $data): string
    {
        return json_encode($data);
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function headers(mixed $data): array
    {
        return [
            'Content-Type' => 'application/json; charset=utf-8'
        ];
    }
}
