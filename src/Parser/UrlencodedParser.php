<?php

declare(strict_types=1);

namespace Verdient\Http\Parser;

use Override;

/**
 * URL编码解析器
 *
 * @author Verdient。
 */
class UrlencodedParser extends AbstractParser
{
    /**
     * @author Verdient。
     */
    #[Override]
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
     * @author Verdient。
     */
    #[Override]
    public function parse(string $content): mixed
    {
        $data = [];
        parse_str($content, $data);
        return $data;
    }
}
