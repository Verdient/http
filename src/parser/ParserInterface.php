<?php

namespace Verdient\http\parser;

/**
 * 解析器接口
 * @author Verdient。
 */
interface ParserInterface
{
    /**
     * 是否可以解析
     * @param string $response 响应内容
     * @return bool
     * @author Verdient。
     */
    public function can($response);

    /**
     * 解析
     * @param string $response 响应内容
     * @return array|bool
     * @author Verdient。
     */
    public function parse($response);
}
