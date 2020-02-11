<?php
namespace Verdient\http\parser;

/**
 * ResponseParser
 * 响应解析器
 * --------------
 * @author Verdient。
 */
abstract class ResponseParser extends \chorus\BaseObject implements ResponseParserInterface
{
	/**
	 * @var String $charset
	 * 字符集
	 * --------------------
	 * @author Verdient。
	 */
	public $chatset = null;
}