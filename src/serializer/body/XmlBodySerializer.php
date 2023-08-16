<?php

namespace Verdient\http\serializer\body;

use DOMDocument;
use DOMElement;
use DomText;

/**
 * XML序列化器
 * @author Verdient。
 */
class XmlBodySerializer implements BodySerializerInterface
{
    /**
     * @var string XML版本
     * @author Verdient。
     */
    const VERSION = '1.0';

    /**
     * @var string ROOT标签
     * @author Verdient。
     */
    const ROOT_TAG = 'request';

    /**
     * @var string 项目标签
     * @author Verdient。
     */
    const ITEM_TAG = 'item';

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public static function serialize($data): string
    {
        if (empty($data)) {
            return '';
        }
        $dom = new DOMDocument(static::VERSION, '');
        $root = new DOMElement(static::ROOT_TAG);
        $dom->appendChild($root);
        static::buildXml($root, $data);
        return $dom->saveXML();
    }

    /**
     * 构建XML
     * @param DOMElement $element 元素
     * @param mixed $data 数据
     * @return array
     * @author Verdient。
     */
    protected static function buildXml($element, $data)
    {
        if (is_array($data) || $data instanceof \Traversable) {
            foreach ($data as $name => $value) {
                if (is_int($name) && is_object($value)) {
                    static::buildXml($element, $value);
                } elseif (is_array($value) || is_object($value)) {
                    $child = new DOMElement(is_int($name) ? static::ITEM_TAG : $name);
                    $element->appendChild($child);
                    static::buildXml($child, $value);
                } else {
                    $child = new DOMElement(is_int($name) ? static::ITEM_TAG : $name);
                    $element->appendChild($child);
                    $child->appendChild(new DOMText((string) $value));
                }
            }
        } else if (is_object($data)) {
            $child = new DOMElement(static::basename(get_class($data)));
            $element->appendChild($child);
            $array = [];
            foreach ($data as $name => $value) {
                $array[$name] = $value;
            }
            static::buildXml($child, $array);
        } else {
            $element->appendChild(new DOMText((string) $data));
        }
    }

    /**
     * 获取类的名称
     * @param string $class 类名
     * @return string
     * @author Verdient。
     */
    public static function basename($class)
    {
        $path = rtrim(str_replace('\\', '/', $class), '/\\');
        if (($pos = mb_strrpos($path, '/')) !== false) {
            return mb_substr($path, $pos + 1);
        }
        return $path;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public static function headers($data): array
    {
        return [
            'Content-Type' => 'application/xml'
        ];
    }
}
