<?php
namespace http\parser;

/**
 * UrlencodedParser
 * URL编码解析器
 * ----------------
 * @author Verdient。
 */
class UrlencodedParser extends ResponseParser
{
	/**
	 * can($response)
	 * 是否可以解析
	 * --------------
	 * @return Boolean
	 * @author Verdient。
	 */
	public function can($response){
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
	 * parse(String $response)
	 * 解析
	 * -----------------------
	 * @param String $response 响应原文
	 * ------------------------------
	 * @return Boolean
	 * @author Verdient。
	 */
	public function parse($response){
		$data = [];
		parse_str($response, $data);
		return $data;
	}
}