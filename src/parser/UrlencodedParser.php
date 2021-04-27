<?php

declare(strict_types=1);

namespace Verdient\http\parser;

/**
 * URL编码解析器
 * @author Verdient。
 */
class UrlencodedParser extends ResponseParser
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function can($response)
    {
        $a = strpos($response, '=');
        $b = strpos($response, '&');
        if($a > 0){
            if($b !== false){
                return $b > $a;
            }
            return true;
        }
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function parse($response)
    {
        $data = [];
        parse_str($response, $data);
        return $data;
    }
}