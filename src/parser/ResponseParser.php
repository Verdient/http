<?php

namespace Verdient\http\parser;

/**
 * 响应解析器
 * @author Verdient。
 */
abstract class ResponseParser extends \chorus\BaseObject implements ResponseParserInterface
{
    /**
     * 字符集
     * @author Verdient。
     */
    public $charset = null;
}
