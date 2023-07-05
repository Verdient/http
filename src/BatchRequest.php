<?php

namespace Verdient\http;

use chorus\BaseObject;
use chorus\Configurable;
use chorus\InvalidConfigException;
use chorus\ObjectHelper;
use Verdient\http\transport\TransportInterface;

/**
 * 批量请求
 * @author Verdient。
 */
class BatchRequest extends BaseObject
{
    use Configurable;

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
     * 批大小
     * @author Verdient。
     */
    public $batchSize = 100;

    /**
     * @var string 传输通道
     * @author Verdient。
     */
    public $transport = 'cUrl';

    /**
     * @var array 传输通道
     * @author Verdient。
     */
    public $transports = [];

    /**
     * @var array 请求集合
     * @author Verdient。
     */
    protected $requests = [];

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function __construct($config = [])
    {
        $this->configuration($config);
    }

    /**
     * 设置请求
     * @param array $requests 请求集合
     * @param int $batchSize 批大小
     * @return BatchRequest
     * @author Verdient。
     */
    public function setRequests($requests, $batchSize = null)
    {
        if (!$batchSize) {
            $batchSize = $this->batchSize;
        }
        $this->requests = array_chunk($requests, $batchSize, true);
        return $this;
    }

    /**
     * 获取传输通道
     * @param $name 通道名称
     * @return TransportInterface
     * @author Verdient。
     */
    public function getTransport()
    {
        foreach ([$this->transports, static::BUILT_IN_TRANSPORTS] as $transports) {
            if (isset($transports[$this->transport])) {
                $transport = ObjectHelper::create($transports[$this->transport]);
                if (!$transport instanceof TransportInterface) {
                    throw new InvalidConfigException('transport must instance of ' . TransportInterface::class);
                }
                return $transport;
            }
        }
        throw new InvalidConfigException('Unknown transport ' . $this->transport);
    }

    /**
     * 发送请求
     * @return array
     */
    public function send()
    {
        $responses = [];
        foreach ($this->requests as $requests) {
            foreach ($requests as $request) {
                $request->trigger(Request::EVENT_BEFORE_REQUEST, $request);
                $request->prepare();
            }
            foreach ($this->getTransport()->batchSend($requests) as $key => $result) {
                list($statusCode, $headers, $content, $response) = $result;
                $request = $requests[$key];
                $request->trigger(Request::EVENT_AFTER_REQUEST);
                $responses[$key] = ObjectHelper::create([
                    'class' => $request::responseClass(),
                    'request' => $request,
                    'tryParse' => $request->tryParse,
                    'parsers' => $request->parsers
                ], $statusCode, $headers, $content, $response);
            }
        }
        return $responses;
    }
}
