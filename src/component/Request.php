<?php
namespace http\component;

use chorus\StringHelper;

/**
 * Request
 * 请求
 * -------
 * @author Verdient。
 */
class Request extends \http\Request
{
	/**
	 * beforeSend()
	 * 准备发送前的操作
	 * -------------
	 * @author Verdient。
	 */
	public function beforeSend(){}

	/**
	 * responseClass()
	 * 响应类
	 * ---------------
	 * @return String
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
	 * prepare()
	 * 准备
	 * ---------
	 * @return Request
	 * @author Verdient。
	 */
	public function prepare(){
		$this->beforeSend();
		return parent::prepare();
	}
}