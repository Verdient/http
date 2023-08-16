<?php

namespace Verdient\http\parser;

/**
 * 响应解析器
 * @author Verdient。
 */
abstract class AbstractParser implements ParserInterface
{
    /**
     * 字符集
     * @author Verdient。
     */
    public $charset = null;
}
