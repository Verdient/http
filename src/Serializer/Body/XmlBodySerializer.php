<?php

namespace Verdient\Http\Serializer\Body;

use DOMDocument;
use DOMElement;
use DomText;

/**
 * XML序列化器
 *
 * @author Verdient。
 */
class XmlBodySerializer implements BodySerializerInterface
{
    /**
     * XML版本
     *
     * @author Verdient。
     */
    const VERSION = '1.0';

    /**
     * ROOT标签
     *
     * @author Verdient。
     */
    const ROOT_TAG = 'request';

    /**
     * 项目标签
     *
     * @author Verdient。
     */
    const ITEM_TAG = 'item';

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function serialize(mixed $data): string
    {
        if (empty($data)) {
            return '';
        }

        $dom = new DOMDocument(static::VERSION, '');
        $root = new DOMElement(static::ROOT_TAG);
        $dom->appendChild($root);

        $this->buildXml($root, $data);

        return $dom->saveXML();
    }

    /**
     * 构建XML
     *
     * @param DOMElement $element 元素
     * @param mixed $data 数据
     * @author Verdient。
     */
    protected function buildXml(DOMElement $element, mixed $data)
    {
        if (is_array($data) || $data instanceof \Traversable) {
            foreach ($data as $name => $value) {
                if (is_int($name) && is_object($value)) {
                    $this->buildXml($element, $value);
                } elseif (is_array($value) || is_object($value)) {
                    $child = new DOMElement(is_int($name) ? static::ITEM_TAG : $name);
                    $element->appendChild($child);
                    $this->buildXml($child, $value);
                } else {
                    $child = new DOMElement(is_int($name) ? static::ITEM_TAG : $name);
                    $element->appendChild($child);
                    $child->appendChild(new DOMText((string) $value));
                }
            }
        } else if (is_object($data)) {
            $child = new DOMElement($this->basename(get_class($data)));
            $element->appendChild($child);
            $array = [];
            foreach ($data as $name => $value) {
                $array[$name] = $value;
            }
            $this->buildXml($child, $array);
        } else {
            $element->appendChild(new DOMText((string) $data));
        }
    }

    /**
     * 获取类的名称
     *
     * @param string $class 类名
     * @author Verdient。
     */
    public function basename($class): string
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
    public function headers(mixed $data): array
    {
        return [
            'Content-Type' => 'application/xml; charset=utf-8'
        ];
    }
}
