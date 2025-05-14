<?php

declare(strict_types=1);

namespace Verdient\Http\Parser;

/**
 * URL编码解析器
 *
 * @author Verdient。
 */
class UrlencodedParser extends AbstractParser
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function can(string $content): bool
    {
        $a = strpos($content, '=');

        if ($a === 0 || $a === false) {
            return false;
        }

        $b = strpos($content, '&');

        if ($b !== false) {
            return $b > $a;
        }

        return true;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function parse(string $content): mixed
    {
        $data = [];
        parse_str($content, $data);
        return $data;
    }
}
