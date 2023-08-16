<?php

namespace Verdient\http;

use Verdient\http\exception\InvalidConfigException;
use Verdient\http\transport\CoroutineTransport;
use Verdient\http\transport\CUrlTransport;
use Verdient\http\transport\TransportInterface;

/**
 * 批量请求
 * @author Verdient。
 */
class BatchRequest
{
    /**
     * @var array 请求集合
     * @author Verdient。
     */
    protected $requests = [];

    /**
     * @param array $requests 请求集合
     * @param int $batchSize 分批大小
     * @author Verdient。
     */
    public function __construct(array $requests, $batchSize = 100)
    {
        $this->requests = array_chunk($requests, $batchSize, true);
    }

    /**
     * @var string 传输通道
     * @author Verdient。
     */
    protected $transport = 'auto';

    /**
     * 设置传输通道
     * @param $name 通道名称
     * @return TransportInterface
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
    }

    /**
     * 获取传输通道
     * @param $name 通道名称
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
     * 发送请求
     * @return array
     */
    public function send()
    {
        $responses = [];
        foreach ($this->requests as $requests) {
            foreach ($this->getTransport()->batchSend($requests) as $key => $result) {
                list($statusCode, $headers, $content, $response) = $result;
                $request = $requests[$key];
                $class = $request::responseClass();
                $responses[$key] = new $class($request, $statusCode, $headers, $content, $response);
            }
        }
        return $responses;
    }
}
