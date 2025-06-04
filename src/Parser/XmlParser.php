<?php

namespace Verdient\Http\Parser;

/**
 * Xml解析器
 *
 * @author Verdient。
 */
class XmlParser extends AbstractParser
{
    /**
     * 参数
     *
     * @author Verdient。
     */
    public int $options = LIBXML_NOCDATA;

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function can(string $content): bool
    {
        $response = trim($content);
        $start = mb_substr($response, 0, 1);
        $end = mb_substr($response, -1);
        return ($start === '<' && $end === '>');
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function parse(string $content): mixed
    {
        $dom = new \DOMDocument('1.0', $this->charset ?: '');

        set_error_handler(function () {});

        $dom->loadXML($content, $this->options);

        restore_error_handler();

        if ($dom->documentElement) {
            return $this->convertXmlToArray(simplexml_import_dom($dom->documentElement));
        }

        return null;
    }

    /**
     * 将XML转换为数组
     *
     * @param string|SimpleXMLElement $xml 要转换的XML
     * @author Verdient。
     */
    protected function convertXmlToArray($xml): array
    {
        if (is_string($xml)) {
            $xml = simplexml_load_string($xml, 'SimpleXMLElement', $this->options);
        }
        $result = (array) $xml;
        foreach ($result as $key => $value) {
            if (!is_scalar($value)) {
                $result[$key] = $this->convertXmlToArray($value);
            }
        }
        return $result;
    }
}
