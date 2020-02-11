<?php
namespace Verdient\http\builder;

/**
 * UrlencodedBuilder
 * Urlencoded构建器
 * -----------------
 * @author Verdient。
 */
class UrlencodedBuilder extends Builder
{
	/**
	 * @var String $contentType
	 * 消息体类型
	 * ------------------------
	 * @inheritdoc
	 * -----------
	 * @author Verdient。
	 */
	public $contentType = 'application/x-www-form-urlencoded';

	/**
	 * @var Integer $encodingType
	 * 编码类型
	 * --------------------------
	 * @author Verdient。
	 */
	public $encodingType = PHP_QUERY_RFC1738;

	/**
	 * toString()
	 * 转为字符串
	 * ----------
	 * @return String
	 * @author Verdient。
	 */
	public function toString(){
		return http_build_query($this->getElements(), '', '&', $this->encodingType);
	}
}