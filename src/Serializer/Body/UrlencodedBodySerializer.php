<?php

namespace Verdient\Http\Serializer\Body;

use Override;

/**
 * Urlencoded消息体序列化器
 *
 * @author Verdient。
 */
class UrlencodedBodySerializer implements BodySerializerInterface
{
    /**
     * @author Verdient。
     */
    #[Override]
    public function serialize(mixed $data): string
    {
        if (empty($data)) {
            return '';
        }

        return http_build_query($data);
    }

    /**
     * @author Verdient。
     */
    #[Override]
    public function headers(mixed $data): array
    {
        return [
            'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'
        ];
    }
}
