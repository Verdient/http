<?php
namespace http\builder;

/**
 * JsonBuilder
 * JSON构建器
 * -----------
 * @author Verdient。
 */
class JsonBuilder extends Builder
{
	/**
	 * @var String $contentType
	 * 消息体类型
	 * ------------------------
	 * @inheritdoc
	 * -----------
	 * @author Verdient。
	 */
	public $contentType = 'application/json';

	/**
	 * toString()
	 * 转为字符串
	 * ----------
	 * @return String
	 * @author Verdient。
	 */
	public function toString(){
		return json_encode($this->getElements());
	}
}