<?php

declare(strict_types=1);

namespace Verdient\http;

use Verdient\http\builder\BuilderInterface;
use Verdient\http\exception\InvalidConfigException;
use Verdient\http\exception\InvalidParamException;
use Verdient\http\serializer\body\BodySerializerInterface;
use Verdient\http\serializer\body\JsonBodySerializer;
use Verdient\http\serializer\query\RFC1738Serializer;
use Verdient\http\serializer\SerializerInterface;
use Verdient\http\traits\Configurable;
use Verdient\http\transport\CoroutineTransport;
use Verdient\http\transport\CUrlTransport;
use Verdient\http\transport\TransportInterface;

/**
 * 请求
 * @author Verdient。
 */
class Request
{
    use Configurable;

    /**
     * @var string 消息体序列化器
     * @author Verdient。
     */
    protected $bodySerializer = JsonBodySerializer::class;

    /**
     * @var string 查询参数序列化器
     * @author Verdient。
     */
    protected $querySerializer = RFC1738Serializer::class;

    /**
     * @var string 传输通道
     * @author Verdient。
     */
    protected $transport = 'auto';

    /**
     * @var string 解析器
     * @author Verdient。
     */
    protected $parser = 'auto';

    /**
     * @var string|null 请求地址
     * @author Verdient。
     */
    protected $url = null;

    /**
     * @var string 请求方法
     * @author Verdient。
     */
    protected $method = 'GET';

    /**
     * @var array 头部参数
     * @author Verdient。
     */
    protected $headers = [];

    /**
     * @var array 查询参数
     * @author Verdient。
     */
    protected $queries = [];

    /**
     * @var array 消息体参数
     * @author Verdient。
     */
    protected $bodies = [];

    /**
     * @var string|null 消息体
     * @author Verdient。
     */
    protected $content = null;

    /**
     * @var string 代理地址
     * @author Verdient。
     */
    protected $proxyHost = null;

    /**
     * @var int 代理地址
     * @author Verdient。
     */
    protected $proxyPort = null;

    /**
     * @var int 超时时间
     * @author Verdient。
     */
    protected $timeout = 15;

    /**
     * @var string|null 协议
     * @author Verdient。
     */
    protected $scheme = null;

    /**
     * @var string|null 主机
     * @author Verdient。
     */
    protected $host = null;

    /**
     * @var int|null 主机
     * @author Verdient。
     */
    protected $port = null;

    /**
     * @var string|null 用户
     * @author Verdient。
     */
    protected $user = null;

    /**
     * @var string|null 密码
     * @author Verdient。
     */
    protected $pass = null;

    /**
     * @var string|null 路径
     * @author Verdient。
     */
    protected $path = null;

    /**
     * @var string|null 片段
     * @author Verdient。
     */
    protected $fragment = null;

    /**
     * 响应类
     * @author Verdient。
     */
    public static function responseClass()
    {
        return Response::class;
    }

    /**
     * 设置传输通道
     * @param $name 通道名称
     * @return static
     * @author Verdient。
     */
    public function setTransport(string $transport)
    {
        if ($transport !== 'auto') {
            if (!class_exists($transport)) {
                throw new InvalidConfigException('Unknown transport: ' . $transport);
            }
            if (!array_key_exists(TransportInterface::class, class_implements($transport))) {
                throw new InvalidConfigException('Transport must implements ' . TransportInterface::class);
            }
        }
        $this->transport = $transport;
        return $this;
    }

    /**
     * 获取传输通道
     * @return TransportInterface
     * @author Verdient。
     */
    protected function getTransport(): TransportInterface
    {
        if ($this->transport === 'auto') {
            if (extension_loaded('swoole')) {
                return new CoroutineTransport;
            } else {
                return new CUrlTransport;
            }
        }
        $class = $this->transport;
        return new $class;
    }

    /**
     * 设置消息体序列化器
     * @param string $serializer 序列化器
     * @return static
     * @author Verdient。
     */
    public function setBodySerializer(string $serializer)
    {
        if (!class_exists($serializer)) {
            throw new InvalidConfigException('Unknown body serializer: ' . $serializer);
        }
        if (!array_key_exists(BodySerializerInterface::class, class_implements($serializer))) {
            throw new InvalidConfigException('Body serializer must implements ' . BodySerializerInterface::class);
        }
        $this->bodySerializer = $serializer;
        return $this;
    }

    /**
     * 获取消息体序列化器
     * @return string
     * @author Verdient。
     */
    public function getBodySerializer(): string
    {
        return $this->bodySerializer;
    }

    /**
     * 设置查询参数序列化器
     * @param string $serializer 解析器
     * @return static
     * @author Verdient。
     */
    public function setQuerySerializer(string $serializer)
    {
        if (!class_exists($serializer)) {
            throw new InvalidConfigException('Unknown query serializer: ' . $serializer);
        }
        if (!array_key_exists(SerializerInterface::class, class_implements($serializer))) {
            throw new InvalidConfigException('Query serializer must implements ' . SerializerInterface::class);
        }
        $this->querySerializer = $serializer;
        return $this;
    }

    /**
     * 获取查询参数序列化器
     * @return string
     * @author Verdient。
     */
    public function getQuerySerializer(): string
    {
        return $this->querySerializer;
    }

    /**
     * 设置解析器
     * @param string $parser 解析器
     * @return static
     * @author Verdient。
     */
    public function setParser(string $parser)
    {
        if ($parser !== 'auto') {
            if (!class_exists($parser)) {
                throw new InvalidConfigException('Unknown parser: ' . $parser);
            }
            if (!array_key_exists(ParserInterface::class, class_implements($parser))) {
                throw new InvalidConfigException('Parser must implements ' . ParserInterface::class);
            }
        }
        $this->parser = $parser;
        return $this;
    }

    /**
     * 获取解析器
     * @return string
     * @author Verdient。
     */
    public function getParser(): string
    {
        return $this->parser;
    }

    /**
     * 设置访问地址
     * @param string $url URL地址
     * @return static
     * @author Verdient。
     */
    public function setUrl(string $url)
    {
        $this->url = null;
        $components = parse_url($url);
        if (!isset($components['scheme']) || !isset($components['host'])) {
            throw new InvalidParamException('Url is not a valid url');
        }
        $this->scheme = $components['scheme'];
        $this->host = $components['host'];
        $this->port = $components['port'] ?? null;
        $this->path = $components['path'] ?? null;
        $this->user = $components['user'] ?? null;
        $this->pass = $components['pass'] ?? null;
        $this->fragment = $components['fragment'] ?? null;
        if (isset($components['query'])) {
            $params = [];
            parse_str($components['query'], $params);
            foreach ($params as $name => $value) {
                $this->addQuery($name, $value);
            }
        }
        return $this;
    }

    /**
     * 获取URL地址
     * @return string
     * @author Verdient。
     */
    public function getUrl(): string
    {
        if ($this->url === null) {
            $url = 'scheme://auth@host:port/path?query#fragment';
            $auth = '';
            if ($this->user) {
                if ($this->pass) {
                    $auth = $this->user . ':' . $this->pass;
                } else {
                    $auth = $this->user;
                }
            }
            $port = $this->port;
            if ($this->scheme == 'http' && $port == 80) {
                $port = null;
            }
            if ($this->scheme == 'https' && $port == 443) {
                $port = null;
            }
            $query = null;
            if (!empty($this->queries)) {
                if (!class_exists($this->querySerializer)) {
                    throw new InvalidConfigException('Unknown query serializer: ' . $this->querySerializer);
                }
                if (!array_key_exists(SerializerInterface::class, class_implements($this->querySerializer))) {
                    throw new InvalidConfigException('Query serializer must implements ' . SerializerInterface::class);
                }
                $class = $this->querySerializer;
                $query = $class::serialize($this->queries);
            }
            foreach ([
                'scheme://' => $this->scheme ? ($this->scheme . '://') : '',
                'auth@' => $auth ? ($auth . '@') : '',
                'host' => $this->host ?: '',
                ':port' => ($port ? ':' . $port : ''),
                '/path' => $this->path ?: '',
                '?query' => $query ? ('?' . $query) : '',
                '#fragment' => $this->fragment ? '#' . $this->fragment : ''
            ] as $name => $value) {
                $url = str_replace($name, $value, $url);
            }
            $this->url = $url;
        }
        return $this->url;
    }

    /**
     * 设置发送的头部参数
     * @param array $headers 头部参数
     * @return static
     * @author Verdient。
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * 获取头部参数
     * @return array
     * @author Verdient。
     */
    public function getHeaders(): array
    {
        if (!in_array($this->getMethod(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            return $this->headers;
        }
        $headers = $this->headers;
        if (!empty($this->bodies)) {
            $class = $this->bodySerializer;
            $headers = array_merge($this->headers, $class::headers($this->bodies));
        }
        $headers['Content-Length'] = strlen($this->getContent());
        return $headers;
    }

    /**
     * 添加头部
     * @param string $name 名称
     * @param string|array $value 值
     * @return static
     * @author Verdient。
     */
    public function addHeader($key, $value)
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * 设置查询信息
     * @param array $queries 查询信息
     * @return static
     * @author Verdient。
     */
    public function setQuery(array $queries)
    {
        $this->url = null;
        $this->queries = $queries;
        return $this;
    }

    /**
     * 获取查询参数
     * @return array
     * @author Verdient。
     */
    public function getQuery(): array
    {
        return $this->queries;
    }

    /**
     * 添加查询信息
     * @param string $name 名称
     * @param mixed $value 内容
     * @return static
     * @author Verdient。
     */
    public function addQuery($name, $value)
    {
        $this->url = null;
        $this->queries[$name] = $value;
        return $this;
    }

    /**
     * 设置消息体参数
     * @param array|BuilderInterface $bodies 消息体
     * @return static
     * @author Verdient。
     */
    public function setBody($bodies)
    {
        $this->content = null;
        if (is_object($bodies)) {
            if (!$bodies instanceof BuilderInterface) {
                throw new InvalidParamException('Body must implements ' . BuilderInterface::class);
            }
            $this->setBodySerializer($bodies->serializer());
        }
        $this->bodies = $bodies;
        return $this;
    }

    /**
     * 获取消息体参数
     * @return array
     * @author Verdient。
     */
    public function getBody(): array
    {
        return $this->bodies;
    }

    /**
     * 添加消息体参数
     * @param string $name 名称
     * @param mixed $value 内容
     * @return static
     * @author Verdient。
     */
    public function addBody($name, $value)
    {
        $this->content = null;
        $this->bodies[$name] = $value;
        return $this;
    }

    /**
     * 设置消息体
     * @param string $data 发送的数据
     * @return static
     * @author Verdient。
     */
    public function setContent(string $data)
    {
        $this->bodies = [];
        $this->content = $data;
        return $this;
    }

    /**
     * 获取消息体
     * @return string
     * @author Verdient。
     */
    public function getContent(): string
    {
        if ($this->content === null) {
            $this->content = '';
            if (empty($this->bodies)) {
                return $this->content;
            }
            $class = $this->bodySerializer;
            $content = $class::serialize($this->bodies);
            if (!is_string($content)) {
                throw new InvalidParamException('Body is unserializable');
            }
            $this->content = $content;
        }
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
     * @return string
     * @author Verdient。
     */
    public function getProxyHost()
    {
        return $this->proxyHost;
    }

    /**
     * 获取代理端口
     * @return int
     * @author Verdient。
     */
    public function getProxyPort()
    {
        return $this->proxyPort;
    }

    /**
     * 设置请求方法
     * @param string $method 请求方法
     * @return static
     * @author Verdient。
     */
    public function setMethod($method)
    {
        $this->method = strtoupper($method);
        return $this;
    }

    /**
     * 获取请求方法
     * @return string
     * @author Verdient。
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * 设置超时时间
     * @param int $timeout 超时时间
     * @return static
     * @author Verdient。
     */
    public function setTimeout(int $timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * 获取超时时间
     * @return int
     * @author Verdient。
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * 发送
     * @return Response
     * @author Verdient。
     */
    public function send()
    {
        list($statusCode, $headers, $content, $response) = $this
            ->getTransport()
            ->send($this);
        $class = static::responseClass();
        return new $class($this, $statusCode, $headers, $content, $response);
    }
}
