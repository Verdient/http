<?php
namespace http;

use chorus\ObjectHelper;
use http\parser\ResponseParserInterface;

/**
 * Response
 * 响应
 * --------
 * @author Verdient。
 */
class Response extends \chorus\BaseObject
{
	/**
	 * @var Boolean $tryParse
	 * 是否尝试解析
	 * -----------------------
	 * @author Verdient。
	 */
	public $tryParse = true;

	/**
	 * @var const BUILT_IN_PARSERS
	 * 内建解析器
	 * ---------------------------
	 * @author Verdient。
	 */
	const BUILT_IN_PARSERS = [
		'application/json' => 'http\parser\JsonParser',
		'application/x-www-form-urlencoded' => 'http\parser\UrlencodedParser',
		'application/xml' => 'http\parser\XmlParser',
	];

	/**
	 * @var Request $request
	 * 请求对象
	 * ---------------------
	 * @author Verdient。
	 */
	public $request;

	/**
	 * @var Array $parsers
	 * 解析器
	 * -------------------
	 * @author Verdient。
	*/
	public $parsers = [];

	/**
	 * @var Integer $_statusCode
	 * 状态码
	 * -------------------------
	 * @author Verdient。
	 */
	public $_statusCode = null;

	/**
	 * @var $_rawHeader
	 * 原始头部
	 * ----------------
	 * @author Verdient。
	 */
	protected $_rawHeader = null;

	/**
	 * @var $_rawContent
	 * 原始消息体
	 * -----------------
	 * @author Verdient。
	 */
	protected $_rawContent = null;

	/**
	 * @var $_body
	 * 消息体
	 * -----------
	 * @author Verdient。
	 */
	protected $_body = false;

	/**
	 * @var $_header
	 * 头部信息
	 * -------------
	 * @author Verdient。
	 */
	protected $_header = false;

	/**
	 * @var String $_contentType
	 * 消息体类型
	 * -------------------------
	 * @author Verdient。
	 */
	protected $_contentType = false;

	/**
	 * @var String $_charset
	 * 字符集
	 * ---------------------
	 * @author Verdient。
	 */
	protected $_charset = false;

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
		$response = $this->request->getResponse();
		$this->_statusCode = $this->request->getStatusCode();
		if($this->request->getOption(CURLOPT_HEADER)){
			$headerSize = $this->request->getInfo(CURLINFO_HEADER_SIZE);
			$this->_rawHeader = mb_substr($response, 0, $headerSize - 4);
			$this->_rawContent = mb_substr($response, $headerSize);
		}else{
			$this->_rawContent = $response;
		}
		$this->parsers = array_merge(static::BUILT_IN_PARSERS, $this->parsers);
	}

	/**
	 * getParser(String $name[, String $charset = null])
	 * 获取解析器
	 * -------------------------------------------------
	 * @param String $name 名称
	 * @param String $charset 字符集
	 * ----------------------------
	 * @return ResponseParserInterface|Boolean
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
	 * getResponse()
	 * 获取响应
	 * -------------
	 * @return String
	 * @author Verdient。
	 */
	public function getRawResponse(){
		return $this->request->getResponse();
	}

	/**
	 * getRawContent()
	 * 获取消息体
	 * ---------------
	 * @return String
	 * @author Verdient。
	 */
	public function getRawContent(){
		return $this->_rawContent;
	}

	/**
	 * getRawHeader()
	 * 获取原始头部
	 * --------------
	 * @return String
	 * @author Verdient。
	 */
	public function getRawHeader(){
		return $this->_rawHeader;
	}

	/**
	 * getBody()
	 * 获取消息体
	 * ---------
	 * @return Array|String|Null
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
							$body = $parser->parse($content);
							if($body){
								$this->_body = $parser->parse($content);
								break;
							}
						}
					}
				}
			}
		}
		return $this->_body;
	}

	/**
	 * getHeaders()
	 * 获取头部
	 * ------------
	 * @return Array|Null
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
	 * getCookie()
	 * 获取Cookie
	 * -----------
	 * @return Array
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
	 * parseCookie(String $cookie)
	 * 解析Cookie
	 * ---------------------------
	 * @param String $cookie cookie
	 * ----------------------------
	 * @return Array
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
	 * getContentType()
	 * 获取消息体类型
	 * ----------------
	 * @return String
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
	 * getCharset()
	 * 获取字符集
	 * ------------
	 * @return String
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
	 * getStatusCode
	 * 获取状态码
	 * -------------
	 * @return Integer
	 * @author Verdient。
	 */
	public function getStatusCode(){
		return $this->_statusCode;
	}

	/**
	 * hasError()
	 * 是否有错误
	 * ----------
	 * @return Boolean
	 * @author Verdient。
	 */
	public function hasError(){
		return $this->request->hasError();
	}

	/**
	 * getError()
	 * 获取错误
	 * ----------
	 * @return Array|Null
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
	 * getErrorMessage()
	 * 获取错误提示信息
	 * -----------------
	 * @return String
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
	 * getErrorCode()
	 * 获取错误码
	 * --------------
	 * @return Mixed
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