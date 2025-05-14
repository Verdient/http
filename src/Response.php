<?php

declare(strict_types=1);

namespace Verdient\Http;

use Iterator;
use Verdient\Http\Parser\JsonParser;
use Verdient\Http\Parser\ParserInterface;
use Verdient\Http\Parser\UrlencodedParser;
use Verdient\Http\Parser\XmlParser;
use Verdient\Http\Transport\Result;

/**
 * 响应
 *
 * @author Verdient。
 */
class Response implements ResponseInterface
{
    /**
     * 内建解析器
     *
     * @author Verdient。
     */
    const BUILT_IN_PARSERS = [
        'application/json' => JsonParser::class,
        'application/x-www-form-urlencoded' => UrlencodedParser::class,
        'application/xml' => XmlParser::class,
    ];

    /**
     * 请求对象
     *
     * @author Verdient。
     */
    protected Request $request;

    /**
     * 状态码
     *
     * @author Verdient。
     */
    protected int $statusCode;

    /**
     * 状态消息
     *
     * @author Verdient。
     */
    protected string $statusMessage;

    /**
     * HTTP版本
     *
     * @author Verdient。
     */
    protected string $httpVersion;

    /**
     * 原始响应
     *
     * @author Verdient。
     */
    protected string $rawReponse;

    /**
     * 原始状态行
     *
     * @author Verdient。
     */
    protected string $rawStatus;

    /**
     * 原始头部
     *
     * @author Verdient。
     */
    protected string $rawHeaders;

    /**
     * 原始消息体
     *
     * @author Verdient。
     */
    protected ?string $rawContent;

    /**
     * 头部数据
     *
     * @author Verdient。
     */
    protected array|null $headers = null;

    /**
     * Cookie数据
     *
     * @author Verdient。
     */
    protected array|null $cookies = null;

    /**
     * 消息体
     *
     * @author Verdient。
     */
    protected mixed $bodies = false;

    /**
     * 消息体类型
     *
     * @author Verdient。
     */
    protected string|null|false $contentType = false;

    /**
     * 字符集
     *
     * @author Verdient。
     */
    protected string|null|false $charset = false;

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function __construct(Request $request, Result $result)
    {
        $this->request = $request;

        $status = $result->status;

        $status = explode(' ', $status);

        if (count($status) > 2) {
            $this->httpVersion = array_shift($status);
            $this->statusCode = (int) array_shift($status);
            $this->statusMessage = implode(' ', $status);
        }

        $this->rawStatus = $result->status;
        $this->rawHeaders = $result->headers;
        $this->rawContent = $result->content;
        $this->rawReponse = $result->response;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function getRawResponse(): string
    {
        return $this->rawReponse;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function getRawStatus(): string
    {
        return $this->rawStatus;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function getRawHeaders(): string
    {
        return $this->rawHeaders;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function getRawContent(): ?string
    {
        return $this->rawContent;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function getHttpVersion(): string
    {
        return $this->httpVersion;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function getStatusMessage(): string
    {
        return $this->statusMessage;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function getHeaders(): array
    {
        if ($this->headers === null) {
            $this->headers = [];

            if (!empty($this->rawHeaders)) {
                $headers = explode("\r\n", $this->rawHeaders);

                foreach ($headers as $header) {

                    if (empty($header)) {
                        continue;
                    }

                    $header = explode(': ', $header);

                    if (count($header) < 2) {
                        continue;
                    }

                    $name = array_shift($header);

                    $value = implode(': ', $header);

                    if (isset($this->headers[$name])) {

                        if (!is_array($this->headers[$name])) {
                            $this->headers[$name] = [$this->headers[$name]];
                        }

                        $this->headers[$name][] = $value;
                    } else {
                        $this->headers[$name] = $value;
                    }
                }
            }
        }

        return $this->headers;
    }

    /**
     * 获取解析器
     *
     * @param string $contentType 消息体类型
     * @param string $charset 字符集
     * @return ParserInterface[]
     * @author Verdient。
     */
    protected function getParsers(?string $contentType, ?string $charset = null): Iterator
    {
        if ($this->request->getParser()) {
            yield $this->request->getParser();
        } else {
            $parsers = [];

            if (!empty($contentType)) {
                if (isset(static::BUILT_IN_PARSERS[$contentType])) {
                    $parsers[] = static::BUILT_IN_PARSERS[$contentType];

                    foreach (static::BUILT_IN_PARSERS as $key => $parser) {
                        if ($key !== $contentType) {
                            $parsers[] = $parser;
                        }
                    }
                } else {
                    $parsers = static::BUILT_IN_PARSERS;
                }
            }

            foreach ($parsers as $parser) {
                $parserInstance = new $parser;

                if (!empty($charset)) {
                    $parserInstance->setCharset($charset);
                }

                yield $parserInstance;
            }
        }
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function getBodies(): mixed
    {
        if ($this->bodies === false) {
            $this->bodies = $this->rawContent;

            if (!empty($this->rawContent)) {
                $content = $this->rawContent;

                if (
                    strlen($content) > 2
                    && ord($content[0]) === 239
                    && ord($content[1]) === 187
                    && ord($content[2]) === 191
                ) {
                    $content = substr($content, 3);
                }

                foreach ($this->getParsers($this->getContentType(), $this->getCharset()) as $parser) {
                    if (!$parser->can($content)) {
                        continue;
                    }
                    try {
                        $bodies = $parser->parse($content);
                        if ($bodies !== false) {
                            $this->bodies = $bodies;
                            break;
                        }
                    } catch (\Throwable) {
                    }
                }
            }
        }
        return $this->bodies;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function getCookies(): array
    {
        if ($this->cookies === null) {

            $this->cookies = [];

            $headers = $this->getHeaders();

            if (isset($headers['Set-Cookie'])) {
                $cookies = $headers['Set-Cookie'];

                if (!empty($cookies)) {

                    if (!is_array($cookies)) {
                        $cookies = [$cookies];
                    }

                    foreach ($cookies as $cookie) {
                        $cookie = $this->parseCookie($cookie);
                        $this->cookies[$cookie['key']] = $cookie;
                    }
                }
            }
        }

        return $this->cookies;
    }

    /**
     * 解析Cookie
     *
     * @param string $cookie Cookie字符串
     * @author Verdient。
     */
    protected function parseCookie(string $cookie): array
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
     * @inheritdoc
     * @author Verdient。
     */
    public function getContentType(): ?string
    {
        if ($this->contentType === false) {

            $this->contentType = null;

            $headers = $this->getHeaders();

            if (isset($headers['Content-Type'])) {
                if (is_array($headers['Content-Type'])) {
                    $this->contentType = explode(';', $headers['Content-Type'][0])[0];
                } else {
                    $this->contentType = explode(';', $headers['Content-Type'])[0];
                }

                if (empty($this->contentType)) {
                    $this->contentType = null;
                }
            }
        }

        return $this->contentType;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function getCharset(): ?string
    {
        if ($this->charset === false) {
            $this->charset = null;
            if ($contentType = $this->getContentType()) {
                if (preg_match('/charset=(.*)/i', $contentType, $matches)) {
                    $this->charset = $matches[1] ?? null;
                }
            }
        }
        return $this->charset;
    }
}
