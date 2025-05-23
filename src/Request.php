<?php

declare(strict_types=1);

namespace Verdient\Http;

use Verdient\Http\Builder\BuilderInterface;
use Verdient\Http\Parser\ParserInterface;
use Verdient\Http\Result;
use Verdient\Http\Serializer\Body\BodySerializerInterface;
use Verdient\Http\Serializer\Body\JsonBodySerializer;
use Verdient\Http\Serializer\Query\RFC1738Serializer;
use Verdient\Http\Serializer\SerializerInterface;
use Verdient\Http\Traits\Configurable;
use Verdient\Http\Transport\CoroutineTransport;
use Verdient\Http\Transport\CUrlTransport;
use Verdient\Http\Transport\Result as TransportResult;
use Verdient\Http\Transport\TransportInterface;

/**
 * 请求
 *
 * @author Verdient。
 */
class Request
{
    use Configurable;

    /**
     * 消息体序列化器
     *
     * @author Verdient。
     */
    protected ?BodySerializerInterface $bodySerializer = null;

    /**
     * 查询参数序列化器
     *
     * @author Verdient。
     */
    protected ?SerializerInterface $querySerializer = null;

    /**
     * 传输通道
     *
     * @author Verdient。
     */
    protected ?TransportInterface $transport = null;

    /**
     * 解析器
     *
     * @author Verdient。
     */
    protected ?ParserInterface $parser = null;

    /**
     * 请求地址
     *
     * @author Verdient。
     */
    protected ?string $url = null;

    /**
     * 请求方法
     *
     * @author Verdient。
     */
    protected string $method = 'GET';

    /**
     * 头部参数
     *
     * @author Verdient。
     */
    protected array $headers = [];

    /**
     * 查询参数
     *
     * @author Verdient。
     */
    protected array $queries = [];

    /**
     * 消息体参数
     *
     * @author Verdient。
     */
    protected array $bodies = [];

    /**
     * 消息体
     *
     * @author Verdient。
     */
    protected string|null|BuilderInterface $content = null;

    /**
     * 代理地址
     *
     * @author Verdient。
     */
    protected ?string $proxyHost = null;

    /**
     * 代理端口
     *
     * @author Verdient。
     */
    protected ?int $proxyPort = null;

    /**
     * 超时时间
     *
     * @author Verdient。
     */
    protected ?int $timeout = 15;

    /**
     * 选项 发送请求后生成
     *
     * @author Verdient。
     */
    protected ?Options $options = null;

    /**
     * 设置访问地址
     *
     * @param string $url URL地址
     * @author Verdient。
     */
    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    /**
     * 获取URL地址
     *
     * @author Verdient。
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * 设置请求方法
     *
     * @param string $method 请求方法
     *
     * @author Verdient。
     */
    public function setMethod($method): static
    {
        $this->method = strtoupper($method);
        return $this;
    }

    /**
     * 获取请求方法
     *
     * @author Verdient。
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * 设置发送的头部参数
     *
     * @param array $headers 头部参数
     * @author Verdient。
     */
    public function setHeaders(array $headers): static
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * 获取头部参数
     *
     * @author Verdient。
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * 添加头部
     * @param string $name 名称
     * @param string|string[] $value 值
     * @return static
     * @author Verdient。
     */
    public function addHeader(string $key, string|array $value)
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * 设置查询信息
     *
     * @param array $queries 查询信息
     * @author Verdient。
     */
    public function setQueries(array $queries): static
    {
        $this->queries = $queries;
        return $this;
    }

    /**
     * 获取查询参数
     *
     * @author Verdient。
     */
    public function getQueries(): array
    {
        return $this->queries;
    }

    /**
     * 添加查询信息
     *
     * @param string $name 名称
     * @param string|string[] $value 内容
     * @author Verdient。
     */
    public function addQuery(string $name, string|array $value): static
    {
        $this->queries[$name] = $value;
        return $this;
    }

    /**
     * 设置消息体参数
     *
     * @param array $bodies 消息体
     * @author Verdient。
     */
    public function setBodies(array $bodies): static
    {
        $this->content = null;

        $this->bodies = $bodies;

        return $this;
    }

    /**
     * 获取消息体参数
     *
     * @author Verdient。
     */
    public function getBodies(): array
    {
        return $this->bodies;
    }

    /**
     * 添加消息体参数
     *
     * @param string $name 名称
     * @param mixed $value 内容
     * @author Verdient。
     */
    public function addBody(string $name, mixed $value): static
    {
        $this->content = null;
        $this->bodies[$name] = $value;
        return $this;
    }

    /**
     * 设置消息体
     *
     * @param string|BuilderInterface $content 发送的数据
     * @author Verdient。
     */
    public function setContent(string|BuilderInterface $content): static
    {
        $this->bodies = [];
        $this->content = $content;
        return $this;
    }

    /**
     * 获取消息体
     *
     * @author Verdient。
     */
    public function getContent(): string|null|BuilderInterface
    {
        return $this->content;
    }

    /**
     * 设置代理
     * @param string $host 地址
     * @param int $port 端口
     * @return static
     * @author Verdient。
     */
    public function setProxy($host, $port = null)
    {
        $this->proxyHost = $host;
        $this->proxyPort = $port;
        return $this;
    }

    /**
     * 获取代理地址
     *
     * @author Verdient。
     */
    public function getProxyHost(): ?string
    {
        return $this->proxyHost;
    }

    /**
     * 获取代理端口
     *
     * @author Verdient。
     */
    public function getProxyPort(): ?int
    {
        return $this->proxyPort;
    }

    /**
     * 设置超时时间
     *
     * @param int $timeout 超时时间
     * @author Verdient。
     */
    public function setTimeout(int $timeout): static
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * 获取超时时间
     *
     * @author Verdient。
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * 设置传输通道
     *
     * @param TransportInterface $transport 传输通道
     * @author Verdient。
     */
    public function setTransport(TransportInterface $transport): static
    {
        $this->transport = $transport;
        return $this;
    }

    /**
     * 获取传输通道
     *
     * @author Verdient。
     */
    protected function getTransport(): ?TransportInterface
    {
        return $this->transport;
    }

    /**
     * 设置消息体序列化器
     *
     * @param BodySerializerInterface $serializer 序列化器
     * @author Verdient。
     */
    public function setBodySerializer(BodySerializerInterface $serializer): static
    {
        $this->bodySerializer = $serializer;
        return $this;
    }

    /**
     * 获取消息体序列化器
     *
     * @author Verdient。
     */
    public function getBodySerializer(): ?BodySerializerInterface
    {
        return $this->bodySerializer;
    }

    /**
     * 设置查询参数序列化器
     *
     * @param SerializerInterface $serializer 序列化器
     * @author Verdient。
     */
    public function setQuerySerializer(SerializerInterface $serializer): static
    {
        $this->querySerializer = $serializer;
        return $this;
    }

    /**
     * 获取查询参数序列化器
     *
     * @author Verdient。
     */
    public function getQuerySerializer(): ?SerializerInterface
    {
        return $this->querySerializer;
    }

    /**
     * 设置解析器
     *
     * @param ParserInterface $parser 解析器
     * @author Verdient。
     */
    public function setParser(ParserInterface $parser): static
    {
        $this->parser = $parser;
        return $this;
    }

    /**
     * 获取解析器
     *
     * @author Verdient。
     */
    public function getParser(): ?ParserInterface
    {
        return $this->parser;
    }

    /**
     * 获取请求选项
     *
     * @author Verdient。
     */
    public function getOptions(): ?Options
    {
        return $this->options;
    }

    /**
     * 创新默认传输实例
     *
     * @author Verdient。
     */
    protected function newDefaultTransport()
    {
        if (
            extension_loaded('swoole')
            && PHP_SAPI === 'cli'
        ) {
            return new CoroutineTransport;
        }

        return new CUrlTransport;
    }

    /**
     * 创建新的默认查询参数序列化器
     *
     * @author Verdient。
     */
    protected function newDefaultQuerySerializer(): SerializerInterface
    {
        return new RFC1738Serializer;
    }

    /**
     * 创建新的默认消息体参数序列化器
     *
     * @author Verdient。
     */
    protected function newDefaultBodySerializer(): BodySerializerInterface
    {
        return new JsonBodySerializer;
    }

    /**
     * 创建新的响应对象
     *
     * @author Verdient。
     */
    protected function newResponse(TransportResult $result): ResponseInterface
    {
        return new Response($this, $result);
    }

    /**
     * 解决URL
     *
     * @author Verdient。
     */
    protected function resolveUrl(): string
    {
        $url = $this->getUrl();

        if (empty($url)) {
            return '';
        }

        $components = parse_url($this->getUrl());

        $scheme = $components['scheme'] ?? null;
        $host = $components['host'] ?? null;
        $port = $components['port'] ?? null;
        $user = $components['user'] ?? null;
        $pass = $components['pass'] ?? null;
        $path = $components['path'] ?? null;
        $fragment = $components['fragment'] ?? null;
        $query = $components['query'] ?? null;

        $auth = '';

        if ($user) {
            if ($pass) {
                $auth = $user . ':' . $pass;
            } else {
                $auth = $user;
            }
        }

        if ($port) {
            $port = (int) $port;
            if ($scheme === 'http' && $port === 80) {
                $port = null;
            }
            if ($scheme === 'https' && $port === 443) {
                $port = null;
            }
        }

        if (!empty($this->queries)) {
            $querySerializer = $this->getQuerySerializer() ?: $this->newDefaultQuerySerializer();

            if ($query) {
                $query .= '&';
            }

            $query .= $querySerializer->serialize($this->queries);
        }

        $url = 'scheme://auth@host:port/path?query#fragment';

        foreach (
            [
                'scheme://' => $scheme ? ($scheme . '://') : '',
                'auth@' => $auth ? ($auth . '@') : '',
                'host' => $host ?: '',
                ':port' => ($port ? ':' . $port : ''),
                '/path' => $path ?: '',
                '?query' => $query ? ('?' . $query) : '',
                '#fragment' => $fragment ? '#' . $fragment : ''
            ] as $name => $value
        ) {
            $url = str_replace($name, $value, $url);
        }

        return $url;
    }

    /**
     * 创建新的选项对象
     *
     * @author Verdient。
     */
    public function newOptions(): Options
    {
        $url = $this->resolveUrl();

        $headers = $this->getHeaders();

        if (empty($this->bodies)) {
            $content = $this->getContent() ?: null;
            if ($content instanceof BuilderInterface) {
                $builder = $content;
                $serializer = $builder->serializer();
                if ($serializer instanceof BodySerializerInterface) {
                    $headers = array_merge($serializer->headers($builder), $headers);
                }
                $content = $serializer->serialize($builder);
            }
        } else {
            $bodySerializer = $this->getBodySerializer() ?: $this->newDefaultBodySerializer();
            $content = $bodySerializer->serialize($this->bodies);
            $headers = array_merge($bodySerializer->headers($this->bodies), $headers);
        }

        $headers['Content-Length'] = $content ? strlen($content) : 0;

        $this->options = new Options(
            url: $url,
            method: $this->getMethod(),
            headers: $headers,
            content: $content,
            timeout: $this->getTimeout(),
            proxyHost: $this->getProxyHost(),
            proxyPort: $this->getProxyPort()
        );

        return $this->options;
    }

    /**
     * 创建新的结果对象
     *
     * @author Verdient。
     */
    public function newResult(TransportResult $result): Result
    {
        if ($result->isOK) {
            return new Result(
                isOK: true,
                response: $this->newResponse($result)
            );
        }

        return new Result(
            isOK: false,
            errorCode: $result->errorCode,
            errorMessage: $result->errorMessage
        );
    }

    /**
     * 发送请求
     *
     * @author Verdient。
     */
    public function send(): Result
    {
        $transport = $this->getTransport() ?: $this->newDefaultTransport();

        return $this->newResult($transport->send($this->newOptions()));
    }
}
