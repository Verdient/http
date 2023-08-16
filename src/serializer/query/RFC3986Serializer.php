<?php

namespace Verdient\http\serializer\query;

use Verdient\http\serializer\SerializerInterface;

/**
 * RFC3986序列化器
 * @author Verdient。
 */
class RFC3986Serializer implements SerializerInterface
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public static function serialize($data): string
    {
        return http_build_query($data, '', '&', PHP_QUERY_RFC3986);
    }
}
