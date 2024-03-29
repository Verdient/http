<?php

namespace Verdient\http\parser;

/**
 * Xml解析器
 * @author Verdient。
 */
class XmlParser extends AbstractParser
{
    /**
     * @var int 参数
     * @author Verdient。
     */
    public $options = LIBXML_NOCDATA;

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function can($response)
    {
        $response = trim($response);
        $start = mb_substr($response, 0, 1);
        $end = mb_substr($response, -1);
        return ($start === '<' && $end === '>');
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function parse($response)
    {
        $dom = new \DOMDocument('1.0', $this->charset ?: '');
        set_error_handler(function () {
        });
        $dom->loadXML($response, $this->options);
        restore_error_handler();
        if ($dom->documentElement) {
            return $this->convertXmlToArray(simplexml_import_dom($dom->documentElement));
        }
        return null;
    }

    /**
     * 将XML转换为数组
     * @param string|SimpleXMLElement $xml 要转换的XML
     * @return array
     * @author Verdient。
     */
    protected function convertXmlToArray($xml)
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
