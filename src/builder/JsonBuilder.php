<?php

namespace Verdient\http\builder;

/**
 * JSON构建器
 * @author Verdient。
 */
class JsonBuilder extends Builder
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public $contentType = 'application/json';

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function toString()
    {
        return json_encode($this->getElements());
    }
}
