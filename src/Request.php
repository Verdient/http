<?php
namespace Verdient\http;

use chorus\InvalidCallException;
use chorus\InvalidConfigException;
use chorus\InvalidParamException;
use chorus\ObjectHelper;
use Verdient\http\builder\Builder;

/**
 * 请求
 * @author Verdient。
 */
class Request extends \chorus\BaseObject
{
	/**
	 * @var string 请求前事件
	 * @author Verdient。
	 */
	const EVENT_BEFORE_REQUEST = 'beforeRequest';

	/**
	 * @var string 请求后事件
	 * @author Verdient。
	 */
	const EVENT_AFTER_REQUEST = 'afterRequest';

	/**
	 * @var array 内建构造器
	 * @author Verdient。
	 */
	const BUILT_IN_BUILDERS = [
		'json' => 'Verdient\http\builder\JsonBuilder',
		'urlencoded' => 'Verdient\http\builder\UrlencodedBuilder',
		'xml' => 'Verdient\http\builder\XmlBuilder'
	];

	/**
	 * @var array 默认参数
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
	 * @var array 构建器
	 * @author Verdient。
	 */
	public $builders = [];

	/**
	 * @var array 解析器
	 * @author Verdient。
	*/
	public $parsers = [];

	/**
	 * @var string|callback 消息体序列化器
	 * @author Verdient。
	 */
	public $bodySerializer = 'json';

	/**
	 * @var bool 是否尝试解析
	 * @author Verdient。
	 */
	public $tryParse = true;

	/**
	 * @var resource cUrl句柄
	 * @author Verdient。
	 */
	protected $_curl = null;

	/**
	 * @var string 请求地址
	 * @author Verdient。
	 */
	protected $_url = null;

	/**
	 * @var string 请求方法
	 * @author Verdient。
	 */
	protected $_method = 'GET';

	/**
	 * @var array 头部参数
	 * @author Verdient。
	 */
	protected $_header = [];

	/**
	 * @var array 查询参数
	 * @author Verdient。
	 */
	protected $_query = [];

	/**
	 * @var array 消息体参数
	 * @author Verdient。
	 */
	protected $_body = [];

	/**
	 * @var string 消息体
	 * @author Verdient。
	 */
	protected $_content = null;

	/**
	 * @var string 响应原文
	 * @author Verdient。
	 */
	protected $_response = null;

	/**
	 * @var array 参数
	 * @author Verdient。
	 */
	protected $_options = [];

	/**
	 * @var bool 是否已发送
	 * @author Verdient。
	 */
	protected $_isSent = false;

	/**
	 * @var int 状态码
	 * @author Verdient。
	 */
	protected $_statusCode = null;

	/**
	 * @var string 响应头部原文
	 * @author Verdient。
	 */
	protected $_responseHeader = null;

	/**
	 * @var string 响应消息体原文
	 * @author Verdient。
	 */
	protected $_responseContent = null;

	/**
	 * @inheritdoc
	 * @author Verdient。
	 */
	public function init(){
		parent::init();
		$this->builders = array_merge(static::BUILT_IN_BUILDERS, $this->builders);
	}

	/**
	 * 响应类
	 * @author Verdient。
	 */
	public static function responseClass(){
		return Response::class;
	}

	/**
	 * 新请求实例
	 * @return Request
	 * @author Verdient。
	 */
	public function new(){
		return new static();
	}

	/**
	 * 获取构建器
	 * @param String $builder 构建器
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
	 * GET访问
	 * @param bool $raw 是否返回原文
	 * @return Response|string
	 * @author Verdient。
	 */
	public function get($raw = false){
		return $this->request('GET', $raw);
	}

	/**
	 * HEAD访问
	 * @param bool $raw 是否返回原文
	 * @return Response|string
	 * @author Verdient。
	 */
	public function head($raw = false){
		return $this->request('HEAD', $raw);
	}

	/**
	 * POST访问
	 * @param bool $raw 是否返回原文
	 * @return Response|string
	 * @author Verdient。
	 */
	public function post($raw = false){
		return $this->request('POST', $raw);
	}

	/**
	 * PUT访问
	 * @param bool $raw 是否返回原文
	 * @return Response|string
	 * @author Verdient。
	 */
	public function put($raw = false){
		return $this->request('PUT', $raw);
	}

	/**
	 * PATCH访问
	 * @param bool $raw 是否返回原文
	 * @return Response|string
	 * @author Verdient。
	 */
	public function patch($raw = false){
		return $this->request('PATCH', $raw);
	}

	/**
	 * DELETE访问
	 * @param bool $raw 是否返回原文
	 * @return Response|string
	 * @author Verdient。
	 */
	public function delete($raw = false){
		return $this->request('DELETE', $raw);
	}

	/**
	 * OPTIONS访问
	 * @param bool $raw 是否返回原文
	 * @return Response|string
	 * @author Verdient。
	 */
	public function options($raw = false){
		return $this->request('OPTIONS', $raw);
	}

	/**
	 * TRACE访问
	 * @param bool $raw 是否返回原文
	 * @return Response|string
	 * @author Verdient。
	 */
	public function trace($raw = false){
		return $this->request('TRACE', $raw);
	}

	/**
	 * 获取URL地址
	 * @return string
	 * @author Verdient。
	 */
	public function getUrl(){
		return $this->_url;
	}

	/**
	 * 设置访问地址
	 * @param String $url URL地址
	 * @return Request
	 * @author Verdient。
	 */
	public function setUrl($url){
		$this->_url = $url;
		return $this;
	}

	/**
	 * 获取头部参数
	 * @return array
	 * @author Verdient。
	 */
	public function getHeader(){
		return $this->_header;
	}

	/**
	 * 设置发送的头部参数
	 * @param array $header 头部参数
	 * @return Request
	 * @author Verdient。
	 */
	public function setHeader(array $header){
		$this->_header = $header;
		return $this;
	}

	/**
	 * 添加头部
	 * @param String $name 名称
	 * @param Mixed $value 值
	 * @return Request
	 * @author Verdient。
	 */
	public function addHeader($key, $value){
		$this->_header[$key] = $value;
		return $this;
	}

	/**
	 * 过滤后将内容添加到头部信息中
	 * @param string $key 名称
	 * @param string $value 内容
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
	 * 获取查询参数
	 * @return array
	 * @author Verdient。
	 */
	public function getQuery(){
		return $this->_query;
	}

	/**
	 * 设置查询信息
	 * @param array $query 查询信息
	 * @return Request
	 * @author Verdient。
	 */
	public function setQuery(array $query){
		$this->_query = $query;
		return $this;
	}

	/**
	 * 添加查询信息
	 * @param String $name 名称
	 * @param Mixed $value 内容
	 * @return Request
	 * @author Verdient。
	 */
	public function addQuery($name, $value){
		$this->_query[$name] = $value;
		return $this;
	}

	/**
	 * 过滤后将内容添加到查询参数中
	 * @param string $key 名称
	 * @param string $value 内容
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
	 * 获取消息体参数
	 * @return array
	 * @author Verdient。
	 */
	public function getBody(){
		return $this->_body;
	}

	/**
	 * 设置消息体参数
	 * @param array $body 消息体
	 * @return Request
	 * @author Verdient。
	 */
	public function setBody(Array $body){
		$this->_body = $body;
		return $this;
	}

	/**
	 * 添加消息体参数
	 * @param string $name 名称
	 * @param mixed $value 内容
	 * @return Request
	 * @author Verdient。
	 */
	public function addBody($name, $value){
		$this->_body[$name] = $value;
		return $this;
	}

	/**
	 * 过滤后将内容添加到消息体中
	 * @param string $key 名称
	 * @param mixed $value 内容
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
	 * 获取消息体
	 * ------------
	 * @return string
	 * @author Verdient。
	 */
	public function getContent(){
		return $this->_content;
	}

	/**
	 * 设置消息体
	 * @param string|array|Builder $data 发送的数据
	 * @param string|callback $serializer 序列化器
	 * @return Request
	 * @author Verdient。
	 */
	public function setContent($data, $serializer = null){
		$this->_content = $this->normalizeContent($data, $serializer);
		return $this;
	}

	/**
	 * 格式化消息体
	 * @param string|array|Builder $data 发送的数据
	 * @param string|callback $serializer 序列化器
	 * @throws Exception
	 * @return string
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
	 * 设置代理
	 * @param string $address 地址
	 * @param int $port 端口
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
	 * 获取请求方法
	 * @return string
	 * @author Verdient。
	 */
	public function getMethod(){
		return $this->_method;
	}

	/**
	 * 设置请求方法
	 * @param string $method 请求方法
	 * @return Request
	 * @author Verdient。
	 */
	public function setMethod($method){
		$this->_method = strtoupper($method);
		return $this;
	}

	/**
	 * 获取响应内容
	 * @return string|null
	 * @author Verdient。
	 */
	public function getResponse(){
		return $this->_response;
	}

	/**
	 * 获取响应头部
	 * @return string|null
	 * @author Verdient。
	 */
	public function getResponseHeader(){
		return $this->_responseHeader;
	}

	/**
	 * 获取响应体
	 * @return string|null
	 * @author Verdient。
	 */
	public function getResponseContent(){
		return $this->_responseContent;
	}

	/**
	 * 设置参数
	 * @param string $name 名称
	 * @param mixed $value 内容
	 * @return Request
	 * @author Verdient。
	 */
	public function setOption($name, $value){
		$this->_options[$name] = $value;
		return $this;
	}

	/**
	 * 批量设置参数
	 * @param array $options 参数集合
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
	 * 删除参数
	 * @param string $name 名称
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
	 * 重置选项
	 * @return Request
	 * @author Verdient。
	 */
	public function resetOptions(){
		$this->_options = [];
		return $this;
	}

	/**
	 * 重置
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
	 * 释放资源
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
	 * 获取选项内容
	 * @param string $name 名称
	 * @return mixed
	 * @author Verdient。
	 */
	public function getOption($name){
		$options = $this->getOptions();
		return isset($options[$name]) ? $options[$name] : false;
	}

	/**
	 * 获取所有的选项
	 * @return array
	 * @author Verdient。
	 */
	public function getOptions(){
		return $this->_options + static::DEFAULT_OPTIONS;
	}

	/**
	 * 获取CURL句柄
	 * @author Verdient。
	 */
	public function getCurl(){
		return $this->_curl;
	}

	/**
	 * 获取连接资源句柄的信息
	 * @param integer $opt 选项名称
	 * @return array|string
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
	 * 是否存在错误
	 * @return bool
	 * @author Verdient。
	 */
	public function hasError(){
		return !!$this->getErrorCode();
	}

	/**
	 * 获取错误码
	 * @return int|null
	 * @author Verdient。
	 */
	public function getErrorCode(){
		if($this->_isSent){
			return curl_errno($this->_curl);
		}
		return null;
	}

	/**
	 * 获取错误类型
	 * @param integer $errorCode 错误码
	 * @return string
	 * @author Verdient。
	 */
	public function getErrorType($errorCode = null){
		return curl_strerror($errorCode ?: $this->getErrorCode());
	}

	/**
	 * 获取错误信息
	 * @return string|null
	 * @author Verdient。
	 */
	public function getErrorMessage(){
		if($this->_isSent){
			return curl_error($this->_curl);
		}
		return null;
	}

	/**
	 * 获取状态码
	 * @return int|null
	 * @author Verdient。
	 */
	public function getStatusCode(){
		if($this->_isSent){
			return (int) $this->getInfo(CURLINFO_HTTP_CODE);
		}
		return null;
	}

	/**
	 * 请求
	 * @param string $method 请求方式
	 * @param bool $raw 是否返回原文
	 * @return Response|string
	 * @author Verdient。
	 */
	public function request($method, $raw = false){
		$this->setMethod($method);
		return $this->send($raw);
	}

	/**
	 * 发送
	 * @param bool $raw 是否返回原文
	 * @return Response|string
	 * @author Verdient。
	 */
	public function send($raw = false){
		if($this->_isSent === false){
			$this->prepareRequest();
			$this->trigger(static::EVENT_BEFORE_REQUEST, $this);
			$this->_isSent = true;
			$response = curl_exec($this->_curl);
			$response = $this->prepareResponse($response);
			$this->trigger(static::EVENT_AFTER_REQUEST, $this);
			$this->releaseResource();
			return $response;
		}else{
			throw new InvalidCallException('The request has been sent. Call reset() or create a new instance');
		}
	}

	/**
	 * 准备响应
	 * @param string $response 响应原文
	 * @param bool $raw 是否返回原文
	 * @author Verdient。
	 */
	public function prepareResponse($response, $raw = false){
		$this->_isSent = true;
		$this->_response = $response;
		$this->_statusCode = (int) $this->getInfo(CURLINFO_HTTP_CODE);
		if($this->getOption(CURLOPT_HEADER)){
			$headerSize = $this->getInfo(CURLINFO_HEADER_SIZE);
			$this->_responseHeader = mb_substr($response, 0, $headerSize - 4);
			$this->_responseContent = mb_substr($response, $headerSize);
		}else{
			$this->_responseContent = $response;
		}
		if($raw === true){
			return $this->_response;
		}
		return ObjectHelper::create([
			'class' => static::responseClass(),
			'request' => $this,
			'tryParse' => $this->tryParse,
			'parsers' => $this->parsers
		]);
	}

	/**
	 * 准备请求方法
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
	 * 准备消息体
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
	 * 准备头部
	 * @return Request
	 * @author Verdient。
	 */
	protected function prepareHeader(){
		$header = [];
		foreach($this->_header as $key => $value){
			$header[] = $key . ':' . $value;
		}
		$header = array_unique($header);
		return $this->setOption(CURLOPT_HTTPHEADER, $header);
	}

	/**
	 * 准备请求URL
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
	 * 准备cURL
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
	 * 准备请求
	 * @return Request
	 * @author Verdient。
	 */
	public function prepareRequest(){
		return $this
			->prepareMethod()
			->prepareUrl()
			->prepareContent()
			->prepareHeader()
			->prepareCUrl();
	}

	/**
	 * 析构时释放资源
	 * @author Verdient。
	 */
	public function __destruct(){
		$this->releaseResource();
	}
}