<?php

namespace Verdient\Http\Serializer\Query;

use Verdient\Http\Serializer\SerializerInterface;

/**
 * RFC3986序列化器
 *
 * @author Verdient。
 */
class RFC3986Serializer implements SerializerInterface
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function serialize(mixed $data): string
    {
        return http_build_query($data, '', '&', PHP_QUERY_RFC3986);
    }
}
