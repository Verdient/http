<?php

namespace Verdient\Http\Serializer\Body;

use Override;

/**
 * JSON消息体序列化器
 *
 * @author Verdient。
 */
class JsonBodySerializer implements BodySerializerInterface
{
    /**
     * @author Verdient。
     */
    #[Override]
    public function serialize(mixed $data): string
    {
        return json_encode($data);
    }

    /**
     * @author Verdient。
     */
    #[Override]
    public function headers(mixed $data): array
    {
        return [
            'Content-Type' => 'application/json; charset=utf-8'
        ];
    }
}
