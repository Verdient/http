<?php
namespace Verdient\http\parser;

/**
 * ResponseParserInterface
 * 响应解析器接口
 * -----------------------
 * @author Verdient。
 */
interface ResponseParserInterface
{
	/**
	 * can(String $response)
	 * 是否可以解析
	 * ---------------------
	 * @param String $response 响应内容
	 * -------------------------------
	 * @return Boolean
	 * @author Verdient。
	 */
	public function can($response);

	/**
	 * parse(String $response)
	 * 解析
	 * -----------------------
	 * @param String $response 响应内容
	 * -------------------------------
	 * @return String|Boolean
	 * @author Verdient。
	 */
	public function parse($response);
}