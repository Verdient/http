<?php

namespace Verdient\Http\Builder;

use Verdient\Http\Serializer\SerializerInterface;

/**
 * 构建器接口
 * @author Verdient。
 */
interface BuilderInterface
{
    /**
     * 序列化器
     *
     * @author Verdient。
     */
    public function serializer(): SerializerInterface;
}
