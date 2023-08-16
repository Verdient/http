<?php

namespace Verdient\http\builder;

/**
 * 构建器接口
 * @author Verdient。
 */
interface BuilderInterface
{
    /**
     * 序列化器
     * @return string
     * @author Verdient。
     */
    public function serializer(): string;
}
