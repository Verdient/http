<?php
namespace Verdient\http\component;

use chorus\ObjectHelper;
use chorus\StringHelper;

/**
 * Component
 * 组件
 * ---------
 * @author Verdient。
 */
class Component extends \chorus\BaseObject
{
	/**
	 * @var $protocol
	 * 协议
	 * --------------
	 * @author Verdient。
	 */
	public $protocol = 'http';

	/**
	 * @var $host
	 * 主机
	 * ----------
	 * @author Verdient。
	 */
	public $host = null;

	/**
	 * @var $port
	 * 端口
	 * ----------
	 * @author Verdient。
	 */
	public $port = null;

	/**
	 * @var $routePrefix
	 * 路由前缀
	 * -----------------
	 * @author Verdient。
	 */
	public $routePrefix = null;

	/**
	 * @var $routes
	 * 路由集合
	 * ------------
	 * @author Verdient。
	 */
	public $routes = [];

	/**
	 * @var String $_requestPath
	 * 请求路径
	 * -------------------------
	 * @author Verdient。
	 */
	protected $_requestPath;

	/**
	 * @var $_requestUrl
	 * 请求地址
	 * -----------------
	 * @author Verdient。
	 */
	protected $_requestUrl = [];

	/**
	 * init()
	 * 初始化
	 * ------
	 * @inheritdoc
	 * -----------
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
	 * generateRequestId()
	 * 生成请求编号
	 * -------------------
	 * @return String
	 * @author Verdient。
	 */
	public function generateRequestId(){
		return hash('sha256', random_bytes(128));
	}

	/**
	 * getRequestPath()
	 * 获取请求路径
	 * ----------------
	 * @return String
	 * @author Verdient。
	 */
	public function getRequestPath(){
		return $this->_requestPath;
	}

	/**
	 * getUrl(String $method)
	 * 获取URL地址
	 * ----------------------
	 * @param String $method 方法
	 * --------------------------
	 * @return String|Null
	 * @author Verdient。
	 */
	public function getUrl($method){
		return isset($this->_requestUrl[$method]) ? $this->_requestUrl[$method] : $this->getRequestPath();
	}

	/**
	 * requestClass()
	 * 请求类
	 * --------------
	 * @return String
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
	 * prepareRequest(String $method[, $requestMethod = 'POST'])
	 * 准备请求
	 * ---------------------------------------------------------
	 * @param String $method 方法
	 * @param String $requestMethod 请求的方法
	 * -------------------------------------
	 * @return Request
	 * @author Verdient。
	 */
	public function prepareRequest($method, $requestMethod = 'POST'){;
		$request = ObjectHelper::create(static::requestClass());
		$request->setUrl($this->getUrl($method));
		$request->setMethod($requestMethod);
		return $request;
	}
}