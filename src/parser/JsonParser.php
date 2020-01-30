<?php
namespace http\parser;

/**
 * JsonParser
 * JSON 解析器
 * ----------
 * @author Verdient。
 */
class JsonParser extends \chorus\BaseObject implements ResponseParserInterface
{
	/**
	 * @var Integer $depth
	 * 递归深度
	 * -------------------
	 * @author Verdient。
	 */
	public $depth = 512;

	/**
	 * @var $options
	 * 参数
	 * -------------
	 * @author Verdient。
	 */
	public $options = 0;

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
		return ($start === '{' && $end === '}') || ($start === '[' && $end === ']');
	}

	/**
	 * parse(String $response)
	 * 解析
	 * -----------------------
	 * @param String $response 响应原文
	 * ------------------------------
	 * @return Boolean
	 * @author Verdient。
	 */
	public function parse($response){
		try{
			return json_decode($response, true, $this->depth, $this->options);
		}catch(\Exception $e){
			return false;
		}
	}
}