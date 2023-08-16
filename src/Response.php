<?php

declare(strict_types=1);

namespace Verdient\http;

use Verdient\http\exception\InvalidConfigException;
use Verdient\http\parser\JsonParser;
use Verdient\http\parser\ParserInterface;
use Verdient\http\parser\UrlencodedParser;
use Verdient\http\parser\XmlParser;

/**
 * 响应
 * @author Verdient。
 */
class Response
{
    /**
     * @var array 内建解析器
     * @author Verdient。
     */
    const BUILT_IN_PARSERS = [
        'application/json' => JsonParser::class,
        'application/x-www-form-urlencoded' => UrlencodedParser::class,
        'application/xml' => XmlParser::class,
    ];

    /**
     * @var Request 请求对象
     * @author Verdient。
     */
    protected $request;

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
    public function __construct(Request $request, $status, $headers, $content, $response)
    {
        $this->request = $request;
        $position = strrpos($status, "\r\n\r\n");
        if ($position !== false) {
            $status = mb_substr($status, $position + 4);
        }
        $status = explode(' ', $status);
        if (count($status) > 2) {
            $this->httpVersion = array_shift($status);
            $this->statusCode = (int) array_shift($status);
            $this->statusMessage = implode(' ', $status);
        }
        $this->rawHeaders = $headers;
        $this->rawContent = $content;
        $this->rawReponse = $response;
    }

    /**
     * 获取请求对象
     * @return Request
     * @author Verdient。
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * 获取解析器
     * @param string $contentType 消息体类型
     * @param string $charset 字符集
     * @return ParserInterface[]
     * @author Verdient。
     */
    protected function getParsers($contentType, $charset = null)
    {
        if ($this->request->getParser() !== 'auto') {
            $parsers = [$this->request->getParser()];
        } else if (isset(static::BUILT_IN_PARSERS[$contentType])) {
            $parsers = [static::BUILT_IN_PARSERS[$contentType]];
            foreach (static::BUILT_IN_PARSERS as $key => $parser) {
                if ($key != $contentType) {
                    $parsers[] = $parser;
                }
            }
        } else {
            $parsers = static::BUILT_IN_PARSERS;
        }
        var_dump($parsers);
        foreach ($parsers as $parser) {
            if (!class_exists($parser)) {
                throw new InvalidConfigException('Unknown Parser: ' . $parser);
            }
            if (!array_key_exists(ParserInterface::class, class_implements($parser))) {
                throw new InvalidConfigException('Parser must implements ' . ParserInterface::class);
            }
            $parser = new $parser;
            if (!empty($charset)) {
                $parser->charset = $charset;
            }
            var_dump(1);
            yield $parser;
        }
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
        if ($this->body === false) {
            $this->body = null;
            if (!$this->rawContent) {
                return $this->body;
            }
            $this->body = $this->rawContent;
            $content = $this->rawContent;
            if (ord(substr($content, 0, 1)) === 239 && ord(substr($content, 1, 1)) === 187 && ord(substr($content, 2, 1)) === 191) {
                $content = substr($content, 3);
            }
            foreach ($this->getParsers($this->getContentType(), $this->getCharset()) as $parser) {
                if (!$parser->can($content)) {
                    continue;
                }
                try {
                    $body = $parser->parse($content);
                    if ($body !== false) {
                        $this->body = $body;
                        break;
                    }
                } catch (\Throwable $e) {
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
        if ($this->headers === false) {
            $this->headers = null;
            if (!$this->rawHeaders) {
                return $this->headers;
            }
            $this->headers = [];
            $headers = explode("\r\n", $this->rawHeaders);
            foreach ($headers as $header) {
                if (!$header) {
                    continue;
                }
                $header = explode(': ', $header);
                if (!isset($header[1])) {
                    continue;
                }
                if (isset($this->headers[$header[0]])) {
                    if (!is_array($this->headers[$header[0]])) {
                        $this->headers[$header[0]] = [$this->headers[$header[0]]];
                    }
                    $this->headers[$header[0]][] = $header[1];
                } else {
                    $this->headers[$header[0]] = $header[1];
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
        if (isset($headers['Set-Cookie'])) {
            if ($cookies = $headers['Set-Cookie']) {
                if (!is_array($cookies)) {
                    $cookies = [$cookies];
                }
                foreach ($cookies as $cookie) {
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
        foreach ($cookie as $element) {
            $elements = explode('=', $element);
            $name = strtolower($elements[0]);
            if (count($elements) === 2) {
                $result[$name] = $elements[1];
            } else {
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
        if ($this->contentType === false) {
            $this->contentType = null;
            $header = $this->getHeaders();
            if (isset($header['Content-Type'])) {
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
        if ($this->charset === false) {
            $this->charset = null;
            $header = $this->getHeaders();
            if (isset($header['Content-Type'])) {
                if (preg_match('/charset=(.*)/i', $header['Content-Type'], $matches)) {
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
