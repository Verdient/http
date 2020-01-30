<?php
namespace http\builder;

use DOMDocument;
use DOMElement;
use DomText;
use SimpleXMLElement;
use chorus\StringHelper;

/**
 * XmlBuilder
 * XML构建器
 * ----------
 * @author Verdient。
 */
class XmlBuilder extends Builder
{
	/**
	 * @var String $version
	 * XML 版本
	 * --------------------
	 * @author Verdient。
	 */
	public $version = '1.0';

	/**
	 * @var String $rootTag
	 * ROOT标签
	 * --------------------
	 * @author Verdient。
	 */
	public $rootTag = 'request';

	/**
	 * @var String $itemTag
	 * 项目标签
	 * --------------------
	 * @author Verdient。
	 */
	public $itemTag = 'item';

	/**
	 * @var String $contentType
	 * 消息体类型
	 * ------------------------
	 * @inheritdoc
	 * -----------
	 * @author Verdient。
	 */
	public $contentType = 'application/xml';

	/**
	 * @var Boolean useTraversableAsArray
	 * 是否将可遍历对象当做数组处理
	 * ----------------------------------
	 * @author Verdient。
	 */
	public $useTraversableAsArray = true;

	/**
	 * toString()
	 * 转为字符串
	 * ----------
	 * @return String
	 * @author Verdient。
	 */
	public function toString(){
		$data = $this->getElements();
		$content = false;
		if(!empty($data)){
			if ($data instanceof DOMDocument) {
				$content = $data->saveXML();
			} elseif ($data instanceof SimpleXMLElement) {
				$content = $data->saveXML();
			} else {
				$dom = new DOMDocument($this->version, $this->charset);
				$root = new DOMElement($this->rootTag);
				$dom->appendChild($root);
				$this->buildXml($root, $data);
				$content = $dom->saveXML();
			}
		}
		return $content;
	}

	/**
	 * buildXml(DOMElement $element, Mixed $data)
	 * 构建XML
	 * ------------------------------------------
	 * @param DOMElement $element 元素
	 * @param Mixed $data 数据
	 * -------------------------------
	 * @return Array
	 * @author Verdient。
	 */
	protected function buildXml($element, $data){
		if(is_array($data) || ($data instanceof \Traversable && $this->useTraversableAsArray)){
			foreach($data as $name => $value){
				if(is_int($name) && is_object($value)){
					$this->buildXml($element, $value);
				}elseif (is_array($value) || is_object($value)){
					$child = new DOMElement(is_int($name) ? $this->itemTag : $name);
					$element->appendChild($child);
					$this->buildXml($child, $value);
				}else{
					$child = new DOMElement(is_int($name) ? $this->itemTag : $name);
					$element->appendChild($child);
					$child->appendChild(new DOMText((string) $value));
				}
			}
		}elseif(is_object($data)){
			$child = new DOMElement(StringHelper::basename(get_class($data)));
			$element->appendChild($child);
			$array = [];
			foreach ($data as $name => $value) {
				$array[$name] = $value;
			}
			$this->buildXml($child, $array);
		}else{
			$element->appendChild(new DOMText((string) $data));
		}
	}
}