<?php

declare(strict_types=1);

namespace Verdient\http;

use chorus\BaseObject;
use chorus\Configurable;
use chorus\HasEvent;
use chorus\InvalidConfigException;
use chorus\InvalidParamException;
use chorus\ObjectHelper;
use Verdient\http\builder\BuilderInterface;
use Verdient\http\transport\TransportInterface;

/**
 * 请求
 * @author Verdient。
 */
class Request extends BaseObject
{
    use Configurable;
    use HasEvent;

    /**
     * @var string 准备前事件
     * @author Verdient。
     */
    const EVENT_BEFORE_PREPARE = 'beforePrepare';

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
     * @var array 内建传输通道
     * @author Verdient。
     */
    const BUILT_IN_TRANSPORTS = [
        'cUrl' => 'Verdient\http\transport\CUrlTransport',
        'coroutine' => 'Verdient\http\transport\CoroutineTransport',
        'stream' => 'Verdient\http\transport\StreamTransport'
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
     * @var array 传输通道
     * @author Verdient。
     */
    public $transports = [];

    /**
     * @var string|callback 消息体序列化器
     * @author Verdient。
     */
    public $bodySerializer = 'json';

    /**
     * @var string 传输通道
     * @author Verdient。
     */
    public $transport = 'cUrl';

    /**
     * @var bool 是否尝试解析
     * @author Verdient。
     */
    public $tryParse = true;

    /**
     * @var string 请求地址
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
    protected $query = [];

    /**
     * @var array 消息体参数
     * @author Verdient。
     */
    protected $body = [];

    /**
     * @var string 消息体
     * @author Verdient。
     */
    protected $content = '';

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
     * @inheritdoc
     * @author Verdient。
     */
    public function __construct($config = [])
    {
        $this->configuration($config);
    }

    /**
     * 响应类
     * @author Verdient。
     */
    public static function responseClass()
    {
        return Response::class;
    }

    /**
     * 获取构建器
     * @param string $builder 构建器
     * @return BuilderInterface
     * @author Verdient。
     */
    public function getBuilder($name)
    {
        foreach([$this->builders, static::BUILT_IN_BUILDERS] as $builders){
            $builder = $builders[strtolower($name)] ?? null;
            if($builder){
                $builder = ObjectHelper::create($builder);
                if(!$builder instanceof BuilderInterface){
                    throw new InvalidConfigException('builder must instance of ' . BuilderInterface::class);
                }
                return $builder;
            }
        }
        throw new InvalidParamException('Unknown builder: ' . $name);
    }

    /**
     * 获取传输通道
     * @param $name 通道名称
     * @return TransportInterface
     * @author Verdient。
     */
    public function getTransport()
    {
        foreach([$this->transports, static::BUILT_IN_TRANSPORTS] as $transports){
            if(isset($transports[$this->transport])){
                $transport = ObjectHelper::create($transports[$this->transport]);
                if(!$transport instanceof TransportInterface){
                    throw new InvalidConfigException('transport must instance of ' . TransportInterface::class);
                }
                return $transport;
            }
        }
        throw new InvalidConfigException('Unknown transport: ' . $this->transport);
    }

    /**
     * 获取URL地址
     * @return string
     * @author Verdient。
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * 设置访问地址
     * @param string $url URL地址
     * @return static
     * @author Verdient。
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * 获取头部参数
     * @return array
     * @author Verdient。
     */
    public function getHeaders()
    {
        return $this->headers;
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
     * 过滤后将内容添加到头部信息中
     * @param string $key 名称
     * @param string $value 内容
     * @return static
     * @author Verdient。
     */
    public function addFilterHeader($key, $value)
    {
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
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * 设置查询信息
     * @param array $query 查询信息
     * @return static
     * @author Verdient。
     */
    public function setQuery(array $query)
    {
        $this->query = $query;
        return $this;
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
        $this->query[$name] = $value;
        return $this;
    }

    /**
     * 过滤后将内容添加到查询参数中
     * @param string $key 名称
     * @param string $value 内容
     * @return static
     * @author Verdient。
     */
    public function addFilterQuery($key, $value)
    {
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
    public function getBody()
    {
        return $this->body;
    }

    /**
     * 设置消息体参数
     * @param array $body 消息体
     * @return static
     * @author Verdient。
     */
    public function setBody(array $body)
    {
        $this->body = $body;
        return $this;
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
        $this->body[$name] = $value;
        return $this;
    }

    /**
     * 过滤后将内容添加到消息体中
     * @param string $key 名称
     * @param mixed $value 内容
     * @return static
     * @author Verdient。
     */
    public function addFilterBody($key, $value)
    {
        if(!empty($value)){
            return $this->addBody($key, $value);
        }
        return $this;
    }

    /**
     * 获取消息体
     * @return string
     * @author Verdient。
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * 设置消息体
     * @param string|array|BuilderInterface $data 发送的数据
     * @param string|callback $serializer 序列化器
     * @return static
     * @author Verdient。
     */
    public function setContent($data, $serializer = null)
    {
        $this->content = $this->normalizeContent($data, $serializer);
        return $this;
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
     * 获取请求方法
     * @return string
     * @author Verdient。
     */
    public function getMethod()
    {
        return $this->method;
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
     * 获取超时时间
     * @return int
     * @author Verdient。
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * 设置超时时间
     * @param string $timeout 超时时间
     * @return static
     * @author Verdient。
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * 重置
     * @return static
     * @author Verdient。
     */
    public function reset()
    {
        $this->url = null;
        $this->method = 'GET';
        $this->headers = [];
        $this->query = [];
        $this->body = [];
        $this->content = null;
        $this->proxyHost = null;
        $this->proxyPort = null;
        $this->timeout = 15;
        return $this;
    }

    /**
     * 请求
     * @param string $method 请求方式
     * @return Response|string
     * @author Verdient。
     */
    public function request($method)
    {
        $this->setMethod($method);
        return $this->send();
    }

    /**
     * 发送
     * @return Response
     * @author Verdient。
     */
    public function send()
    {
        $this->trigger(static::EVENT_BEFORE_PREPARE, $this);
        $this->prepare();
        $this->trigger(static::EVENT_BEFORE_REQUEST, $this);
        list($statusCode, $headers, $content, $response) = $this->getTransport()->send($this);
        $result = ObjectHelper::create([
            'class' => static::responseClass(),
            'request' => $this,
            'tryParse' => $this->tryParse,
            'parsers' => $this->parsers
        ], $statusCode, $headers, $content, $response);
        $this->trigger(static::EVENT_AFTER_REQUEST, $this, $result);
        return $result;
    }

    /**
     * 准备请求
     * @return static
     * @author Verdient。
     */
    public function prepare()
    {
        $this->url = $this->normalizeUrl($this->url);
        if(in_array($this->method, ['POST', 'PUT', 'DELETE', 'PATCH'])){
            if(empty($this->content) && !empty($this->body)){
                $this->content = $this->normalizeContent($this->body, $this->bodySerializer);
            }
            $this->addHeader('Content-Length', strlen($this->content));
        }
        return $this;
    }

    /**
     * 格式化URL
     * @param string $url URL地址
     * @return string
     * @author Verdient。
     */
    public function normalizeUrl($url)
    {
        $url = parse_url($url);
        foreach(['scheme', 'host'] as $name){
            if(!isset($url[$name])){
                throw new InvalidParamException('Url is not a valid url');
            }
        }
        $query = [];
        if(isset($url['query'])){
            parse_str($url['query'], $query);
        }
        if(!empty($this->query)){
            $query = array_merge($query, $this->query);
        }
        $url = $url['scheme'] . '://' .
            (isset($url['user']) ? $url['user'] : '') .
            (isset($url['pass']) ? ((isset($url['user']) ? ':' : '') . $url['pass']) : '') .
            ((isset($url['pass']) || isset($url['pass'])) ? '@' : '') .
            $url['host'] .
            (isset($url['port']) ? ':' . $url['port'] : '') .
            (isset($url['path']) ? $url['path'] : '') .
            (!empty($query) ? ('?' . http_build_query($query)) : '') .
            (isset($url['fragment']) ? ('#' . $url['fragment']) : '');
        return $url;
    }

    /**
     * 格式化消息体
     * @param string|array|BuilderInterface $data 发送的数据
     * @param string|callback $serializer 序列化器
     * @throws Exception
     * @return string
     * @author Verdient。
     */
    public function normalizeContent($data, $serializer = null)
    {
        if(is_callable($serializer)){
            $data = call_user_func($serializer, $data);
        }else if(is_string($serializer) && !empty($serializer) && is_array($data)){
            $builder = $this->getBuilder($serializer);
            $builder->setElements($data);
            $data = $builder;
        }
        if($data instanceof BuilderInterface){
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
}