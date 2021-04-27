<?php
namespace Verdient\http\builder;

/**
 * Urlencoded构建器
 * @author Verdient。
 */
class UrlencodedBuilder extends Builder
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public $contentType = 'application/x-www-form-urlencoded';

    /**
     * @var int 编码类型
     * @author Verdient。
     */
    public $encodingType = PHP_QUERY_RFC1738;

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function toString()
    {
        return http_build_query($this->getElements(), '', '&', $this->encodingType);
    }
}