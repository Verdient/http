<?php
namespace Verdient\http\component;

use chorus\StringHelper;

/**
 * 请求
 * @author Verdient。
 */
class Request extends \Verdient\http\Request
{
	/**
	 * @var Component|null 组件
	 * @author Verdient。
	 */
	public $component = null;

	/**
	 * 准备发送前的操作
	 * @author Verdient。
	 */
	public function beforeSend(){}

	/**
	 * 响应类
	 * @return string
	 * @author Verdient。
	 */
	public static function responseClass(){
		$requestClass = static::class;
		$namespace = StringHelper::dirname($requestClass);
		$baseName = StringHelper::basename($requestClass);
		$baseName = str_ireplace('Request', 'Response', $baseName);
		$responseClass = $namespace . '\\' . $baseName;
		if(class_exists($responseClass)){
			return $responseClass;
		}
		return Response::class;
	}

	/**
	 * @inheritdoc
	 * @author Verdient。
	 */
	public function prepare(){
		$this->beforeSend();
		return parent::prepare();
	}
}