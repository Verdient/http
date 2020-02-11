<?php
namespace Verdient\http\parser;

/**
 * XmlParser
 * Xml解析器
 * ---------
 * @author Verdient。
 */
class XmlParser extends ResponseParser
{
	/**
	 * @var String $charset
	 * 字符集
	 * --------------------
	 * @inheritdoc
	 * -----------
	 * @author Verdient。
	 */
	public $charset = '';

	/**
	 * @var Integer $options
	 * 参数
	 * ---------------------
	 * @author Verdient。
	 */
	public $options = LIBXML_NOCDATA;

	/**
	 * can($response)
	 * 是否可以解析
	 * --------------
	 * @return Boolean
	 * @author Verdient。
	 */
	public function can($response){
		$response = trim($response);
		$start = mb_substr($response, 0, 1);
		$end = mb_substr($response, -1);
		return ($start === '<' && $end === '>');
	}

	/**
	 * parse(String $response)
	 * 解析
	 * ----------------------
	 * @param String $reponse 响应原文
	 * ------------------------------
	 * @return Boolean
	 * @author Verdient。
	 */
	public function parse($response){
		$dom = new \DOMDocument('1.0', $this->charset);
		$dom->loadXML($response, $this->options);
		return $this->convertXmlToArray(simplexml_import_dom($dom->documentElement));
	}

	/**
	 * convertXmlToArray(String|SimpleXMLElement $xml)
	 * 将XML转换为数组
	 * -----------------------------------------------
	 * @param String|SimpleXMLElement $xml 要转换的XML
	 * ----------------------------------------------
	 * @return Array
	 * @author Verdient。
	 */
	protected function convertXmlToArray($xml){
		if(is_string($xml)){
			$xml = simplexml_load_string($xml, 'SimpleXMLElement', $this->options);
		}
		$result = (array) $xml;
		foreach($result as $key => $value){
			if(!is_scalar($value)){
				$result[$key] = $this->convertXmlToArray($value);
			}
		}
		return $result;
	}
}