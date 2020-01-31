<?php
namespace http;

use chorus\InvalidCallException;
use chorus\InvalidConfigException;
use chorus\InvalidParamException;
use chorus\ObjectHelper;
use http\builder\Builder;

/**
 * Request
 * 请求
 * -------
 * @author Verdient。
 */
class Request extends \chorus\BaseObject
{
	/**
	 * @var const BUILT_IN_BUILDERS
	 * 内建构造器
	 * ----------------------------
	 * @author Verdient。
	 */
	const BUILT_IN_BUILDERS = [
		'json' => 'http\builder\JsonBuilder',
		'urlencoded' => 'http\builder\UrlencodedBuilder',
		'xml' => 'http\builder\XmlBuilder'
	];

	/**
	 * @var const DEFAULT_OPTIONS
	 * 模式属性
	 * --------------------------
	 * @author Verdient。
	 */
	const DEFAULT_OPTIONS = [
		CURLOPT_TIMEOUT => 30,
		CURLOPT_CONNECTTIMEOUT => 30,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_HEADER => true,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_HTTPHEADER => []
	];

	/**
	 * @var Array $builders
	 * 构建器
	 * --------------------
	 * @author Verdient。
	 */
	public $builders = [];

	/**
	 * @var Array $parsers
	 * 解析器
	 * -------------------
	 * @author Verdient。
	*/
	public $parsers = [];

	/**
	 * @var String|Callable $bodySerializer
	 * 消息体序列化器
	 * ------------------------------------
	 * @author Verdient。
	 */
	public $bodySerializer = 'json';

	/**
	 * @var Boolean $tryParse
	 * 是否尝试解析
	 * ----------------------
	 * @author Verdient。
	 */
	public $tryParse = true;

	/**
	 * @var $_curl
	 * cUrl实例
	 * -----------
	 * @author Verdient。
	 */
	protected $_curl = null;

	/**
	 * @var String $_url
	 * 请求地址
	 * -----------------
	 * @author Verdient。
	 */
	protected $_url = null;

	/**
	 * @var String $_method
	 * 请求方法
	 * --------------------
	 * @author Verdient。
	 */
	protected $_method = 'GET';

	/**
	 * @var Array $_header
	 * 头部参数
	 * -------------------
	 * @author Verdient。
	 */
	protected $_header = [];

	/**
	 * @var Array $_query
	 * 查询参数
	 * ------------------
	 * @author Verdient。
	 */
	protected $_query = [];

	/**
	 * @var Array $_body
	 * 消息体参数
	 * -----------------
	 * @author Verdient。
	 */
	protected $_body = [];

	/**
	 * @var String $_content
	 * 消息体
	 * ---------------------
	 * @author Verdient。
	 */
	protected $_content = null;

	/**
	 * @var String $_response
	 * 响应原文
	 * ----------------------
	 * @author Verdient。
	 */
	protected $_response = null;

	/**
	 * @var Array $_options
	 * 参数
	 * --------------------
	 * @author Verdient。
	 */
	protected $_options = [];

	/**
	 * @var Boolean $_isSent
	 * 是否已发送
	 * ---------------------
	 * @author Verdient。
	 */
	protected $_isSent = false;

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
		$this->builders = array_merge(static::BUILT_IN_BUILDERS, $this->builders);
	}

	/**
	 * responseClass()
	 * 响应类
	 * ---------------
	 * @author Verdient。
	 */
	public static function responseClass(){
		return Response::class;
	}

	/**
	 * new()
	 * 新请求实例
	 * --------
	 * @return Request
	 * @author Verdient。
	 */
	public function new(){
		return new static();
	}

	/**
	 * getBuilder(String $builder)
	 * 获取构建器
	 * ---------------------------
	 * @param String $builder 构建器
	 * ----------------------------
	 * @return Builder
	 * @author Verdient。
	 */
	public function getBuilder($name){
		$builder = strtolower($name);
		$builder = isset($this->builders[$builder]) ? $this->builders[$builder] : null;
		if($builder){
			$builder = ObjectHelper::create($builder);
			if(!$builder instanceof Builder){
				throw new InvalidConfigException('builder must instance of ' . Builder::class);
			}
			return $builder;
		}
		throw new InvalidParamException('Unkrown builder: ' . $name);
	}

	/**
	 * get([Boolean $raw = false])
	 * GET访问
	 * ---------------------------
	 * @param Boolean $raw 是否返回原文
	 * ------------------------------
	 * @return Response|String
	 * @author Verdient。
	 */
	public function get($raw = false){
		return $this->request('GET', $raw);
	}

	/**
	 * head([Boolean $raw = false])
	 * HEAD访问
	 * ----------------------------
	 * @param Boolean $raw 是否返回原文
	 * ------------------------------
	 * @return Response|String
	 * @author Verdient。
	 */
	public function head($raw = false){
		return $this->request('HEAD', $raw);
	}

	/**
	 * post([Boolean $raw = false])
	 * POST访问
	 * ----------------------------
	 * @param Boolean $raw 是否返回原文
	 * ------------------------------
	 * @return Response|String
	 * @author Verdient。
	 */
	public function post($raw = false){
		return $this->request('POST', $raw);
	}

	/**
	 * put([Boolean $raw = false])
	 * PUT访问
	 * ---------------------------
	 * @param Boolean $raw 是否返回原文
	 * ------------------------------
	 * @return Response|String
	 * @author Verdient。
	 */
	public function put($raw = false){
		return $this->request('PUT', $raw);
	}

	/**
	 * patch([Boolean $raw = false])
	 * PATCH访问
	 * -----------------------------
	 * @param Boolean $raw 是否返回原文
	 * ------------------------------
	 * @return Response|String
	 * @author Verdient。
	 */
	public function patch($raw = false){
		return $this->request('PATCH', $raw);
	}

	/**
	 * delete([Boolean $raw = false])
	 * DELETE访问
	 * ------------------------------
	 * @param Boolean $raw 是否返回原文
	 * ------------------------------
	 * @return Response|String
	 * @author Verdient。
	 */
	public function delete($raw = false){
		return $this->request('DELETE', $raw);
	}

	/**
	 * options([Boolean $raw = false])
	 * OPTIONS访问
	 * -------------------------------
	 * @param Boolean $raw 是否返回原文
	 * ------------------------------
	 * @return Response|String
	 * @author Verdient。
	 */
	public function options($raw = false){
		return $this->request('OPTIONS', $raw);
	}

	/**
	 * trace([Boolean $raw = false])
	 * TRACE访问
	 * -----------------------------
	 * @param Boolean $raw 是否返回原文
	 * ------------------------------
	 * @return Response|String
	 * @author Verdient。
	 */
	public function trace($raw = false){
		return $this->request('TRACE', $raw);
	}

	/**
	 * getUrl()
	 * 获取URL地址
	 * ----------
	 * @return String
	 * @author Verdient。
	 */
	public function getUrl(){
		return $this->_url;
	}

	/**
	 * setUrl(String $url)
	 * 设置访问地址
	 * -------------------
	 * @param String $url URL
	 * ----------------------
	 * @return Request
	 * @author Verdient。
	 */
	public function setUrl($url){
		$this->_url = $url;
		return $this;
	}

	/**
	 * getHeader()
	 * 获取头部参数
	 * -----------
	 * @return Array
	 * @author Verdient。
	 */
	public function getHeader(){
		return $this->_header;
	}

	/**
	 * setHeader(Array $header)
	 * 设置发送的头部信息
	 * ------------------------
	 * @param Array $header 头部信息
	 * ----------------------------
	 * @return Request
	 * @author Verdient。
	 */
	public function setHeader(Array $header){
		$this->_header = $header;
		return $this;
	}

	/**
	 * addHeader(String $name, Mixed $value)
	 * 添加头部
	 * -------------------------------------
	 * @param String $name 名称
	 * @param Mixed $value 值
	 * ------------------------
	 * @return Request
	 * @author Verdient。
	 */
	public function addHeader($key, $value){
		$this->_header[$key] = $value;
		return $this;
	}

	/**
	 * addFilterHeader(String $key, String $value)
	 * 过滤后将内容添加到头部信息中
	 * -------------------------------------------
	 * @param String $key 名称
	 * @param String $value 内容
	 * -------------------------
	 * @return Request
	 * @author Verdient。
	 */
	public function addFilterHeader($key, $value){
		if(!empty($value)){
			return $this->addHeader($key, $value);
		}
		return $this;
	}

	/**
	 * getQuery()
	 * 获取查询参数
	 * ----------
	 * @return Array
	 * @author Verdient。
	 */
	public function getQuery(){
		return $this->_query;
	}

	/**
	 * setQuery(Array $query)
	 * 设置查询信息
	 * ----------------------
	 * @param Array $query 查询信息
	 * ---------------------------
	 * @return Request
	 * @author Verdient。
	 */
	public function setQuery(Array $query){
		$this->_query = $query;
		return $this;
	}

	/**
	 * addQuery(String $name, Mixed $value)
	 * 添加查询信息
	 * ------------------------------------
	 * @param String $name 名称
	 * @param Mixed $value 内容
	 * ------------------------
	 * @return Request
	 * @author Verdient。
	 */
	public function addQuery($name, $value){
		$this->_query[$name] = $value;
		return $this;
	}

	/**
	 * addFilterQuery(String $key, String $value)
	 * 过滤后将内容添加到查询参数中
	 * ------------------------------------------
	 * @param String $key 名称
	 * @param String $value 内容
	 * -------------------------
	 * @return Request
	 * @author Verdient。
	 */
	public function addFilterQuery($key, $value){
		if(!empty($value)){
			return $this->addQuery($key, $value);
		}
		return $this;
	}

	/**
	 * getBody()
	 * 获取消息体参数
	 * ------------
	 * @return Array
	 * @author Verdient。
	 */
	public function getBody(){
		return $this->_body;
	}

	/**
	 * setBody(Array $body)
	 * 设置消息体参数
	 * -----------------------
	 * @param Array $body 消息体
	 * ------------------------
	 * @return Request
	 * @author Verdient。
	 */
	public function setBody(Array $body){
		$this->_body = $body;
		return $this;
	}

	/**
	 * addBody(String $name, Mixed $value)
	 * 设置消息体参数
	 * -----------------------------------
	 * @param String $name 名称
	 * @param Mixed $value 内容
	 * ------------------------
	 * @return Request
	 * @author Verdient。
	 */
	public function addBody($name, $value){
		$this->_body[$name] = $value;
		return $this;
	}

	/**
	 * addFilterBody(String $key, String $value)
	 * 过滤后将内容添加到消息体中
	 * -----------------------------------------
	 * @param String $key 名称
	 * @param String $value 内容
	 * ------------------------
	 * @return Request
	 * @author Verdient。
	 */
	public function addFilterBody($key, $value){
		if(!empty($value)){
			return $this->addBody($key, $value);
		}
		return $this;
	}

	/**
	 * getContent()
	 * 获取消息体
	 * ------------
	 * @return String
	 * @author Verdient。
	 */
	public function getContent(){
		return $this->_content;
	}

	/**
	 * setContent(String|Array|Builder $data, String|Callable $serializer = null)
	 * 设置消息体
	 * --------------------------------------------------------------------------
	 * @param String|Array|Builder $data 发送的数据
	 * @param String|Callable $serializer 序列化器
	 * ------------------------------------------
	 * @return Request
	 * @author Verdient。
	 */
	public function setContent($data, $serializer = null){
		$this->_content = $this->normalizeContent($data, $serializer);
		return $this;
	}

	/**
	 * normalizeContent(String|Array|Builde $data, String|Callable $serializer = null)
	 * 格式化消息体
	 * -------------------------------------------------------------------------------
	 * @param String|Array|Builde $data 发送的数据
	 * @param String|Callable $serializer 序列化器
	 * ------------------------------------------
	 * @throws Exception
	 * @return String
	 * @author Verdient。
	 */
	public function normalizeContent($data, $serializer = null){
		if(is_callable($serializer)){
			$data = call_user_func($serializer, $data);
		}else if(is_array($data) && is_string($serializer) && !empty($serializer)){
			$builder = $this->getBuilder($serializer);
			$builder->setElements($data);
			$data = $builder;
		}
		if($data instanceof Builder){
			foreach($data->headers() as $name => $value){
				$this->addHeader($name, $value);
			}
			$data = $data->toString();
		}
		if(!is_string($data)){
			throw new InvalidParamException('content must be a string');
		}
		return $data;
	}

	/**
	 * setProxy(String $address, Integer $port)
	 * 设置代理
	 * ----------------------------------------
	 * @param String $address 地址
	 * @param Integer $port 端口
	 * ---------------------------
	 * @return Request
	 * @author Verdient。
	 */
	public function setProxy($address, $port = null){
		$this->setOption(CURLOPT_PROXY, $address);
		if($port){
			$this->setOption(CURLOPT_PROXYPORT, $port);
		}
		return $this;
	}

	/**
	 * getMethod()
	 * 获取请求方法
	 * -----------
	 * @return String
	 * @author Verdient。
	 */
	public function getMethod(){
		return $this->_method;
	}

	/**
	 * setMethod(String $method)
	 * 设置请求方法
	 * -------------------------
	 * @param String $method 请求方法
	 * -----------------------------
	 * @return Request
	 * @author Verdient。
	 */
	public function setMethod($method){
		$this->_method = strtoupper($method);
		return $this;
	}

	/**
	 * getResponse()
	 * 获取响应内容
	 * -------------
	 * @author Verdient。
	 */
	public function getResponse(){
		return $this->_response;
	}

	/**
	 * setOption(String $name, Mixed $value)
	 * 设置选项
	 * -------------------------------------
	 * @param String $name 名称
	 * @param Mixed $value 内容
	 * ------------------------
	 * @return Request
	 * @author Verdient。
	 */
	public function setOption($name, $value){
		$this->_options[$name] = $value;
		return $this;
	}

	/**
	 * setOptions(Array $options)
	 * 批量设置选项
	 * --------------------------
	 * @param Array $options 选项集合
	 * -------------------------------
	 * @return Request
	 * @author Verdient。
	 */
	public function setOptions(Array $options){
		foreach($options as $name => $value){
			$this->setOption($name, $value);
		}
		return $this;
	}

	/**
	 * unsetOption(String $name)
	 * 删除选项
	 * -------------------------
	 * @param String $name 名称
	 * ------------------------
	 * @return Request
	 * @author Verdient。
	 */
	public function unsetOption($name){
		if(isset($this->_options[$name])){
			unset($this->_options[$name]);
		}
		return $this;
	}

	/**
	 * resetOptions()
	 * 重置选项
	 * --------------
	 * @return Request
	 * @author Verdient。
	 */
	public function resetOptions(){
		$this->_options = [];
		return $this;
	}

	/**
	 * reset()
	 * 重置
	 * -------
	 * @return Request
	 * @author Verdient。
	 */
	public function reset(){
		$this->releaseResource();
		$this->_url = null;
		$this->_header = [];
		$this->_query = [];
		$this->_body = [];
		$this->_content = null;
		$this->_options = [];
		$this->_response = null;
		$this->_isSent = false;
		return $this;
	}

	/**
	 * releaseResource()
	 * 释放资源
	 * -----------------
	 * @return Request
	 * @author Verdient。
	*/
	public function releaseResource(){
		if($this->_curl !== null){
			@curl_close($this->_curl);
			$this->_curl = null;
		}
	}

	/**
	 * getOption(String $name)
	 * 获取选项内容
	 * -----------------------
	 * @param String $name 名称
	 * -----------------------
	 * @return Mixed
	 * @author Verdient。
	 */
	public function getOption($name){
		$options = $this->getOptions();
		return isset($options[$name]) ? $options[$name] : false;
	}

	/**
	 * getOptions()
	 * 获取所有的选项
	 * ------------
	 * @return Array
	 * @author Verdient。
	 */
	public function getOptions(){
		return $this->_options + static::DEFAULT_OPTIONS;
	}

	/**
	 * getInfo([Integer $opt = null])
	 * 获取连接资源句柄的信息
	 * ------------------------------
	 * @param Integer $opt 选项名称
	 * --------------------------
	 * @return Array|String
	 * @author Verdient。
	 */
	public function getInfo($opt = null){
		if($this->_curl !== null){
			return curl_getinfo($this->_curl, $opt);
		}else{
			return $opt ? null : [];
		}
	}

	/**
	 * hasError()
	 * 是否存在错误
	 * ----------
	 * @return Boolean
	 * @author Verdient。
	 */
	public function hasError(){
		return !!$this->getErrorCode();
	}

	/**
	 * getErrorCode()
	 * 获取错误码
	 * --------------
	 * @return Integer
	 * @author Verdient。
	 */
	public function getErrorCode(){
		return curl_errno($this->_curl);
	}

	/**
	 * getErrorType([Integer $errorCode = null])
	 * 获取错误类型
	 * -----------------------------------------
	 * @param Integer $errorCode 错误码
	 * -------------------------------
	 * @return String
	 * @author Verdient。
	 */
	public function getErrorType($errorCode = null){
		return curl_strerror($errorCode ?: $this->getErrorCode());
	}

	/**
	 * getErrorMessage()
	 * 获取错误信息
	 * ----=------------
	 * @return String
	 * @author Verdient。
	 */
	public function getErrorMessage(){
		return curl_error($this->_curl);
	}

	/**
	 * getStatusCode()
	 * 获取状态码
	 * ---------------
	 * @return Integer
	 * @author Verdient。
	 */
	public function getStatusCode(){
		return (int) $this->getInfo(CURLINFO_HTTP_CODE);
	}

	/**
	 * request(String $method[, Boolean $raw = false])
	 * 请求
	 * -----------------------------------------------
	 * @param String $method 请求方式
	 * -----------------------------
	 * @return Response|String
	 * @author Verdient。
	 */
	public function request($method, $raw = false){
		$this->setMethod($method);
		return $this->send($raw);
	}

	/**
	 * send([Boolean $raw = false])
	 * 发送
	 * ----------------------------
	 * @param Boolean $raw 是否返回原文
	 * ------------------------------
	 * @return String
	 * @author Verdient。
	 */
	public function send($raw = false){
		if($this->_isSent === false){
			$this->_isSent = true;
			$this->prepare();
			$this->_response = curl_exec($this->_curl);
			if($raw === true){
				return $this->_response;
			}
			return ObjectHelper::create([
				'class' => static::responseClass(),
				'request' => $this,
				'tryParse' => $this->tryParse,
				'parsers' => $this->parsers
			]);
		}else{
			throw new InvalidCallException('The request has been sent. Call reset() or create a new instance');
		}
	}

	/**
	 * prepareMethod()
	 * 准备请求方法
	 * ---------------
	 * @return Request
	 * @author Verdient。
	 */
	protected function prepareMethod(){
		if($this->_method === 'HEAD'){
			$this->setOption(CURLOPT_NOBODY, true);
			$this->unsetOption(CURLOPT_WRITEFUNCTION);
		}
		if(!in_array($this->_method, ['POST', 'PUT', 'DELETE', 'PATCH'])){
			$this->unsetOption(CURLOPT_POSTFIELDS);
			$this->unsetOption(CURLOPT_POST);
		}
		return $this->setOption(CURLOPT_CUSTOMREQUEST, $this->_method);
	}

	/**
	 * prepareContent()
	 * 准备消息体
	 * ----------------
	 * @return Request
	 * @author Verdient。
	 */
	protected function prepareContent(){
		if(in_array($this->_method, ['POST', 'PUT', 'DELETE', 'PATCH'])){
			if(!empty($this->_content)){
				$content = $this->_content;
			}else if(!empty($this->_body)){
				$content = $this->normalizeContent($this->_body, $this->bodySerializer);
			}
			if(!empty($content)){
				$this->setOption(CURLOPT_POST, true);
				$this->setOption(CURLOPT_POSTFIELDS, $content);
				$this->addHeader('Content-Length', strlen($content));
			}
		}
		return $this;
	}

	/**
	 * prepareHeader()
	 * 准备头部
	 * ---------------
	 * @return Request
	 * @author Verdient。
	 */
	protected function prepareHeader(){
		$header = [];
		foreach($this->_header as $name => $value){
			$header[] = $name . ':' . $value;
		}
		$header = array_unique($header);
		return $this->setOption(CURLOPT_HTTPHEADER, $header);
	}

	/**
	 * prepareUrl()
	 * 准备请求URL
	 * ---------------
	 * @return Request
	 * @author Verdient。
	 */
	protected function prepareUrl(){
		$url = parse_url($this->_url);
		foreach(['scheme', 'host'] as $name){
			if(!isset($url[$name])){
				throw new InvalidParamException('Url is not a valid url');
			}
		}
		$query = isset($url['query']) ? ('?' . $url['query']) : '';
		if(!empty($this->_query)){
			$query .= ($query ? '&' : '?') . http_build_query($this->_query);
		}
		$url =
			$url['scheme'] .
			'://' .
			(isset($url['user']) ? $url['user'] : '') .
			(isset($url['pass']) ? ((isset($url['user']) ? ':' : '') . $url['pass']) : '') .
			((isset($url['pass']) || isset($url['pass'])) ? '@' : '') .
			$url['host'] .
			(isset($url['path']) ? $url['path'] : '') .
			$query .
			(isset($url['fragment']) ? ('#' . $url['fragment']) : '');
		return $this->setOption(CURLOPT_URL, $url);
	}

	/**
	 * prepareCUrl()
	 * 准备cURL
	 * -------------
	 * @author Verdient。
	 */
	protected function prepareCUrl(){
		$options = [];
		foreach($this->getOptions() as $key => $value){
			if(is_numeric($key)){
				$options[$key] = $value;
			}
		}
		$this->_curl = curl_init();
		curl_setopt_array($this->_curl, $options);
		return $this;
	}

	/**
	 * prepare()
	 * 准备
	 * ---------
	 * @return Request
	 * @author Verdient。
	 */
	public function prepare(){
		return $this
			->prepareMethod()
			->prepareUrl()
			->prepareContent()
			->prepareHeader()
			->prepareCUrl();
	}

	/**
	 * __destruct()
	 * 析构函数
	 * ------------
	 * @author Verdient。
	 */
	public function __destruct(){
		$this->releaseResource();
	}
}