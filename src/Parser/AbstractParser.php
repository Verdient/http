<?php

namespace Verdient\Http\Parser;

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
     * @inheritdoc
     * @author Verdient。
     */
    public function setCharset(string $value): static
    {
        $this->charset = $value;

        return $this;
    }
}
