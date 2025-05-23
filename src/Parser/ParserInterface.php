<?php

namespace Verdient\Http\Parser;

/**
 * 解析器接口
 *
 * @author Verdient。
 */
interface ParserInterface
{
    /**
     * 设置字符集
     *
     * @author Verdient。
     */
    public function setCharset(string $value): static;

    /**
     * 是否可以解析
     *
     * @param string $content 响应内容
     * @author Verdient。
     */
    public function can(string $content): bool;

    /**
     * 解析
     *
     * @param string $content 响应内容
     * @author Verdient。
     */
    public function parse(string $content): mixed;
}
