<?php

namespace Verdient\Http\Serializer\Query;

use Verdient\Http\Serializer\SerializerInterface;

/**
 * RFC1738序列化器
 *
 * @author Verdient。
 */
class RFC1738Serializer implements SerializerInterface
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function serialize(mixed $data): string
    {
        return http_build_query($data, '', '&', PHP_QUERY_RFC1738);
    }
}
