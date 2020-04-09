<?php
namespace Verdient\http\component;

use chorus\ObjectHelper;
use chorus\StringHelper;

/**
 * 组件
 * @author Verdient。
 */
class Component extends \chorus\BaseObject
{
	/**
	 * @var string 协议方法
	 * @author Verdient。
	 */
	public $protocol = 'http';

	/**
	 * @var string 主机域名
	 * @author Verdient。
	 */
	public $host = null;

	/**
	 * @var string 端口
	 * @author Verdient。
	 */
	public $port = null;

	/**
	 * @var string 路由前缀
	 * @author Verdient。
	 */
	public $routePrefix = null;

	/**
	 * @var string 路由集合
	 * @author Verdient。
	 */
	public $routes = [];

	/**
	 * @var string 请求路径
	 * @author Verdient。
	 */
	protected $_requestPath;

	/**
	 * @var string 请求地址集合
	 * @author Verdient。
	 */
	protected $_requestUrl = [];

	/**
	 * @inheritdoc
	 * @author Verdient。
	 */
	public function init(){
		parent::init();
		if(!$this->host){
			throw new \Exception('host must be set');
		}
		if($this->protocol == 'http' && $this->port == 80){
			$this->port = null;
		}
		if($this->protocol == 'https' && $this->port == 443){
			$this->port = null;
		}
		$this->_requestPath = $this->protocol . '://' . $this->host . ($this->port ? (':' . $this->port) : '') . ($this->routePrefix ? '/' . $this->routePrefix : '');
		foreach($this->routes as $name => $route){
			$this->_requestUrl[$name] = $this->_requestPath . '/' . $route;
		}
	}

	/**
	 * 获取请求路径
	 * @return string
	 * @author Verdient。
	 */
	public function getRequestPath(){
		return $this->_requestPath;
	}

	/**
	 * 获取URL地址
	 * @param string $name 名称
	 * @return string|null
	 * @author Verdient。
	 */
	public function getUrl($name){
		return isset($this->_requestUrl[$name]) ? $this->_requestUrl[$name] : $this->getRequestPath();
	}

	/**
	 * 请求类
	 * @return string
	 * @author Verdient。
	 */
	public static function requestClass(){
		$requestClass = static::class;
		$namespace = StringHelper::dirname($requestClass);
		$baseName = StringHelper::basename($requestClass);
		$class = $namespace . '\\' . $baseName . 'Request';
		if(class_exists($class)){
			return $class;
		}
		return Request::class;
	}

	/**
	 * 准备请求
	 * @param string $name 方法
	 * @param string $method 请求的方法
	 * @return Request
	 * @author Verdient。
	 */
	public function prepareRequest($name, $method = 'POST'){;
		$request = ObjectHelper::create(static::requestClass());
		$request->setUrl($this->getUrl($name));
		$request->setMethod($method);
		return $request;
	}
}