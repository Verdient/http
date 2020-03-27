<?php
namespace Verdient\http;

use chorus\ObjectHelper;
use Verdient\http\parser\ResponseParserInterface;

/**
 * 响应
 * @author Verdient。
 */
class Response extends \chorus\BaseObject
{
	/**
	 * @var array 内建解析器
	 * @author Verdient。
	 */
	const BUILT_IN_PARSERS = [
		'application/json' => 'Verdient\http\parser\JsonParser',
		'application/x-www-form-urlencoded' => 'Verdient\http\parser\UrlencodedParser',
		'application/xml' => 'Verdient\http\parser\XmlParser',
	];

	/**
	 * @var bool 是否尝试解析
	 * @author Verdient。
	 */
	public $tryParse = true;

	/**
	 * @var Request 请求对象
	 * @author Verdient。
	 */
	public $request;

	/**
	 * @var array 解析器
	 * @author Verdient。
	*/
	public $parsers = [];

	/**
	 * @var int 状态码
	 * @author Verdient。
	 */
	public $_statusCode = null;

	/**
	 * @var string 原始响应
	 * @author Verdient。
	 */
	protected $_rawReponse = null;

	/**
	 * @var string 原始头部
	 * @author Verdient。
	 */
	protected $_rawHeader = null;

	/**
	 * @var string 原始消息体
	 * @author Verdient。
	 */
	protected $_rawContent = null;

	/**
	 * @var mixed 消息体
	 * @author Verdient。
	 */
	protected $_body = false;

	/**
	 * @var array 头部信息
	 * @author Verdient。
	 */
	protected $_header = false;

	/**
	 * @var string 消息体类型
	 * @author Verdient。
	 */
	protected $_contentType = false;

	/**
	 * @var string 字符集
	 * @author Verdient。
	 */
	protected $_charset = false;

	/**
	 * @inheritdoc
	 * @author Verdient。
	 */
	public function init(){
		parent::init();
		$this->_statusCode = $this->request->getStatusCode();
		$this->_rawReponse = $this->request->getResponse();
		$this->_rawHeader = $this->request->getResponseHeader();
		$this->_rawContent = $this->request->getResponseContent();
		$this->parsers = array_merge(static::BUILT_IN_PARSERS, $this->parsers);
	}

	/**
	 * 获取解析器
	 * @param string $name 名称
	 * @param string $charset 字符集
	 * @return ResponseParserInterface|bool
	 * @author Verdient。
	 */
	public function getParser($name, $charset = null){
		$parser = strtolower($name);
		$parser = isset($this->parsers[$parser]) ? $this->parsers[$parser] : null;
		if($parser){
			$parser = ObjectHelper::create($parser);
			if(!$parser instanceof ResponseParserInterface){
				throw new \Exception('parser must implements ' . ResponseParserInterface::class);
			}
			if(!empty($charset)){
				$parser->charset = $charset;
			}
			return $parser;
		}
		return false;
	}

	/**
	 * 获取响应
	 * @return string
	 * @author Verdient。
	 */
	public function getRawResponse(){
		return $this->request->getResponse();
	}

	/**
	 * 获取消息体
	 * @return string
	 * @author Verdient。
	 */
	public function getRawContent(){
		return $this->_rawContent;
	}

	/**
	 * 获取原始头部
	 * @return string
	 * @author Verdient。
	 */
	public function getRawHeader(){
		return $this->_rawHeader;
	}

	/**
	 * 获取消息体
	 * @return array|string|null
	 * @author Verdient。
	 */
	public function getBody(){
		if($this->_body === false){
			$this->_body = null;
			if($this->_rawContent){
				$this->_body = $this->_rawContent;
				$content = $this->_rawContent;
				if(ord(substr($content, 0, 1)) === 239 && ord(substr($content, 1, 1)) === 187 && ord(substr($content, 2, 1)) === 191){
					$content = substr($content, 3);
				}
				$parsed = false;
				$parser = $this->getParser($this->getContentType(), $this->getCharset());
				if($parser){
					$body = $parser->parse($content);
					if($body){
						$parsed = true;
						$this->_body = $parser->parse($content);
					}
				}
				if(!$parsed && $this->tryParse === true){
					foreach(array_keys($this->parsers) as $name){
						$parser = $this->getParser($name);
						if($parser->can($content)){
							try{
								$body = $parser->parse($content);
								if($body){
									$this->_body = $parser->parse($content);
									break;
								}
							}catch(\Exception $e){}catch(\Error $e){}
						}
					}
				}
			}
		}
		return $this->_body;
	}

	/**
	 * 获取头部
	 * @return array|null
	 * @author Verdient。
	 */
	public function getHeader(){
		if($this->_header === false){
			$this->_header = null;
			if($this->_rawHeader){
				$this->_header = [];
				$headers = explode("\r\n", $this->_rawHeader);
				foreach($headers as $header){
					if($header){
						$header = explode(': ', $header);
						if(isset($header[1])){
							if(isset($this->_header[$header[0]])){
								if(!is_array($this->_header[$header[0]])){
									$this->_header[$header[0]] = [$this->_header[$header[0]]];
								}
								$this->_header[$header[0]][] = $header[1];
							}else{
								$this->_header[$header[0]] = $header[1];
							}
						}
					}
				}
			}
		}
		return $this->_header;
	}

	/**
	 * 获取Cookie
	 * @return array
	 * @author Verdient。
	 */
	public function getCookie(){
		$result = [];
		if($cookies = $this->getHeader('Set-Cookie')){
			if(!is_array($cookies)){
				$cookies = [$cookies];
			}
			foreach($cookies as $cookie){
				$cookie = $this->parseCookie($cookie);
				$result[$cookie['key']] = $cookie;
			}
		}
		return $result;
	}

	/**
	 * 解析Cookie
	 * @param string $cookie cookie
	 * @return array
	 * @author Verdient。
	 */
	public function parseCookie($cookie){
		$cookie = explode('; ', $cookie);
		$keyValue = explode('=', $cookie[0]);
		unset($cookie[0]);
		$result['key'] = $keyValue[0];
		$result['value'] = urldecode($keyValue[1]);
		foreach($cookie as $element){
			$elements = explode('=', $element);
			$name = strtolower($elements[0]);
			if(count($elements) === 2){
				$result[$name] = $elements[1];
			}else{
				$result[$name] = true;
			}
		}
		return $result;
	}

	/**
	 * 获取消息体类型
	 * @return string
	 * @author Verdient。
	 */
	public function getContentType(){
		if($this->_contentType === false){
			$this->_contentType = null;
			$header = $this->getHeader();
			if(isset($header['Content-Type'])){
				$this->_contentType = explode(';', $header['Content-Type'])[0];
			}
		}
		return $this->_contentType;
	}

	/**
	 * 获取字符集
	 * @return string
	 * @author Verdient。
	 */
	public function getCharset(){
		if($this->_charset === false){
			$this->_charset = null;
			$header = $this->getHeader();
			if(isset($header['Content-Type'])){
				if(preg_match('/charset=(.*)/i', $header['Content-Type'], $matches)){
					$this->_charset = $matches[1];
				}
			}
		}
		return $this->_charset;
	}

	/**
	 * 获取状态码
	 * @return int
	 * @author Verdient。
	 */
	public function getStatusCode(){
		return $this->_statusCode;
	}

	/**
	 * 是否有错误
	 * @return bool
	 * @author Verdient。
	 */
	public function hasError(){
		return $this->request->hasError();
	}

	/**
	 * 获取错误
	 * @return array|null
	 * @author Verdient。
	 */
	public function getError(){
		if($this->hasError()){
			if($this->request->hasError()){
				$code = $this->request->getErrorCode();
				return [
					'code' => $code,
					'type' => $this->request->getErrorType($code),
					'message' => $this->request->getErrorMessage()
				];
			}else{
				return [];
			}
		}
		return null;
	}

	/**
	 * 获取错误提示信息
	 * @return string
	 * @author Verdient。
	 */
	public function getErrorMessage(){
		if($this->hasError()){
			$error = $this->getError();
			if(isset($error['message'])){
				return $error['message'];
			}else{
				return 'Unknown Error';
			}
		}
		return null;
	}

	/**
	 * 获取错误码
	 * @return mixed
	 * @author Verdient。
	 */
	public function getErrorCode(){
		if($this->hasError()){
			$error = $this->getError();
			if(isset($error['code'])){
				return $error['code'];
			}else{
				return $this->getStatusCode();
			}
		}
		return null;
	}
}