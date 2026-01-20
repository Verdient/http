<?php

namespace Verdient\Http\Parser;

use Override;

/**
 * 抽象解析器
 *
 * @author Verdient。
 */
abstract class AbstractParser implements ParserInterface
{
    /**
     * 字符集
     *
     * @author Verdient。
     */
    protected ?string $charset = null;

    /**
     * @author Verdient。
     */
    #[Override]
    public function setCharset(string $value): static
    {
        $this->charset = $value;

        return $this;
    }
}
