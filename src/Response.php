<?php

declare(strict_types=1);

namespace Verdient\http;

use chorus\BaseObject;
use chorus\ObjectHelper;
use Verdient\http\parser\ResponseParserInterface;

/**
 * 响应
 * @author Verdient。
 */
class Response extends BaseObject
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
    protected $statusCode = null;

    /**
     * @var string 状态消息
     * @author Verdient。
     */
    protected $statusMessage = null;

    /**
     * @var string HTTP版本
     * @author Verdient。
     */
    protected $httpVersion = null;

    /**
     * @var string 原始响应
     * @author Verdient。
     */
    protected $rawReponse = null;

    /**
     * @var string 原始头部
     * @author Verdient。
     */
    protected $rawHeaders = null;

    /**
     * @var string 原始消息体
     * @author Verdient。
     */
    protected $rawContent = null;

    /**
     * @var mixed 消息体
     * @author Verdient。
     */
    protected $body = false;

    /**
     * @var array 头部信息
     * @author Verdient。
     */
    protected $headers = false;

    /**
     * @var string 消息体类型
     * @author Verdient。
     */
    protected $contentType = false;

    /**
     * @var string 字符集
     * @author Verdient。
     */
    protected $charset = false;

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function __construct($status, $headers, $content, $response)
    {
        $position = strrpos($status, "\r\n\r\n");
        if($position !== false){
            $status = mb_substr($status, $position + 4);
        }
        $status = explode(' ', $status);
        if(count($status) > 2){
            $this->httpVersion = array_shift($status);
            $this->statusCode = (int) array_shift($status);
            $this->statusMessage = implode(' ', $status);
        }
        $this->rawHeaders = $headers;
        $this->rawContent = $content;
        $this->rawReponse = $response;
    }

    /**
     * 获取解析器
     * @param string $name 名称
     * @param string $charset 字符集
     * @return ResponseParserInterface|false
     * @author Verdient。
     */
    protected function getParser($name, $charset = null)
    {
        if($name){
            foreach([$this->parsers, static::BUILT_IN_PARSERS] as $parsers){
                $parser = $parsers[strtolower($name)] ?? null;
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
            }
        }
        return false;
    }

    /**
     * 获取响应
     * @return string
     * @author Verdient。
     */
    public function getRawResponse()
    {
        return $this->rawReponse;
    }

    /**
     * 获取消息体
     * @return string
     * @author Verdient。
     */
    public function getRawContent()
    {
        return $this->rawContent;
    }

    /**
     * 获取原始头部
     * @return string
     * @author Verdient。
     */
    public function getRawHeaders()
    {
        return $this->rawHeaders;
    }

    /**
     * 获取消息体
     * @return array|string|null
     * @author Verdient。
     */
    public function getBody()
    {
        if($this->body === false){
            $this->body = null;
            if($this->rawContent){
                $this->body = $this->rawContent;
                $content = $this->rawContent;
                if(ord(substr($content, 0, 1)) === 239 && ord(substr($content, 1, 1)) === 187 && ord(substr($content, 2, 1)) === 191){
                    $content = substr($content, 3);
                }
                $parsed = false;
                $parser = $this->getParser($this->getContentType(), $this->getCharset());
                if($parser){
                    $body = $parser->parse($content);
                    if($body){
                        $parsed = true;
                        $this->body = $parser->parse($content);
                    }
                }
                if(!$parsed && $this->tryParse === true){
                    foreach([$this->parsers, static::BUILT_IN_PARSERS] as $parsers){
                        foreach(array_keys($parsers) as $name){
                            $parser = $this->getParser($name);
                            if($parser->can($content)){
                                try{
                                    $body = $parser->parse($content);
                                    if($body){
                                        $this->body = $parser->parse($content);
                                        break;
                                    }
                                }catch(\Throwable $e){}
                            }
                        }
                    }
                }
            }
        }
        return $this->body;
    }

    /**
     * 获取头部
     * @return array|null
     * @author Verdient。
     */
    public function getHeaders()
    {
        if($this->headers === false){
            $this->headers = null;
            if($this->rawHeaders){
                $this->headers = [];
                $headers = explode("\r\n", $this->rawHeaders);
                foreach($headers as $header){
                    if($header){
                        $header = explode(': ', $header);
                        if(isset($header[1])){
                            if(isset($this->headers[$header[0]])){
                                if(!is_array($this->headers[$header[0]])){
                                    $this->headers[$header[0]] = [$this->headers[$header[0]]];
                                }
                                $this->headers[$header[0]][] = $header[1];
                            }else{
                                $this->headers[$header[0]] = $header[1];
                            }
                        }
                    }
                }
            }
        }
        return $this->headers;
    }

    /**
     * 获取Cookies
     * @return array
     * @author Verdient。
     */
    public function getCookies()
    {
        $result = [];
        $headers = $this->getHeaders();
        if(isset($headers['Set-Cookie'])){
            if($cookies = $headers['Set-Cookie']){
                if(!is_array($cookies)){
                    $cookies = [$cookies];
                }
                foreach($cookies as $cookie){
                    $cookie = $this->parseCookie($cookie);
                    $result[$cookie['key']] = $cookie;
                }
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
    public function parseCookie($cookie)
    {
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
    public function getContentType()
    {
        if($this->contentType === false){
            $this->contentType = null;
            $header = $this->getHeaders();
            if(isset($header['Content-Type'])){
                $this->contentType = explode(';', $header['Content-Type'])[0];
            }
        }
        return $this->contentType;
    }

    /**
     * 获取字符集
     * @return string
     * @author Verdient。
     */
    public function getCharset()
    {
        if($this->charset === false){
            $this->charset = null;
            $header = $this->getHeaders();
            if(isset($header['Content-Type'])){
                if(preg_match('/charset=(.*)/i', $header['Content-Type'], $matches)){
                    $this->charset = $matches[1];
                }
            }
        }
        return $this->charset;
    }

    /**
     * 获取状态码
     * @return int
     * @author Verdient。
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * 获取状态消息
     * @return int
     * @author Verdient。
     */
    public function getStatusMessage()
    {
        return $this->statusMessage;
    }

    /**
     * 获取HTTP版本
     * @return int
     * @author Verdient。
     */
    public function getHttpVersion()
    {
        return $this->httpVersion;
    }
}