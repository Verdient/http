<?php

declare(strict_types=1);

namespace Verdient\Http\Parser;

/**
 * JSON 解析器
 *
 * @author Verdient。
 */
class JsonParser extends AbstractParser
{
    /**
     * 递归深度
     *
     * @author Verdient。
     */
    public int $depth = 512;

    /**
     * 选项
     *
     * @author Verdient。
     */
    public int $options = 0;

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function can(string $content): bool
    {
        $content = trim($content);
        $start = mb_substr($content, 0, 1);
        $end = mb_substr($content, -1);
        return ($start === '{' && $end === '}') || ($start === '[' && $end === ']');
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function parse(string $content): mixed
    {
        try {
            return json_decode($content, true, $this->depth, $this->options);
        } catch (\Exception $e) {
            return false;
        }
    }
}
