<?php
namespace Verdient\http\parser;

/**
 * JSON 解析器
 * @author Verdient。
 */
class JsonParser extends ResponseParser
{
	/**
	 * @var int 递归深度
	 * @author Verdient。
	 */
	public $depth = 512;

	/**
	 * @var int 参数
	 * @author Verdient。
	 */
	public $options = 0;

	/**
	 * @inheritdoc
	 * @author Verdient。
	 */
	public function can($response){
		$response = trim($response);
		$start = mb_substr($response, 0, 1);
		$end = mb_substr($response, -1);
		return ($start === '{' && $end === '}') || ($start === '[' && $end === ']');
	}

	/**
	 * @inheritdoc
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