<?php

namespace Verdient\Http;

use Verdient\Http\Transport\CoroutineTransport;
use Verdient\Http\Transport\CUrlTransport;
use Verdient\Http\Transport\TransportInterface;

/**
 * 批量请求
 *
 * @author Verdient。
 */
class BatchRequest
{
    /**
     * 传输通道
     *
     * @author Verdient。
     */
    protected ?TransportInterface $transport = null;

    /**
     * @param array<int|string,Request> $requests 请求集合
     * @param int $batchSize 批处理大小
     * @author Verdient。
     */
    public function __construct(
        protected array $requests,
        protected int $batchSize = 100
    ) {}

    /**
     * 设置批处理大小
     *
     * @param int $value 批处理大小
     * @author Verdient。
     */
    public function setBatchSize(int $value): static
    {
        $this->batchSize = $value;

        return $this;
    }

    /**
     * 获取批处理大小
     *
     * @author Verdient。
     */
    public function getBatchSize(): int
    {
        return $this->batchSize;
    }

    /**
     * 设置传输通道
     *
     * @param TransportInterface $transport 传输通道
     *
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
     * 发送请求
     *
     * @return array<int|string,Result>
     * @author Verdient。
     */
    public function send(): array
    {
        $transport = $this->getTransport() ?: $this->newDefaultTransport();

        $result = [];

        foreach (array_chunk($this->requests, $this->batchSize, true) as $requests) {
            foreach ($this->batchSend($requests, $transport) as $key => $response) {
                $result[$key] = $response;
            }
        }

        return $result;
    }

    /**
     * 批量发送请求
     *
     * @param array<int|string,Request> $requests 请求集合
     * @param TransportInterface $transport 传输通道
     * @return array<int|string,Result>
     * @author Verdient。
     */
    protected function batchSend(array $requests, TransportInterface $transport): array
    {
        $batchOptions = array_map(fn($request) => $request->newOptions(), $requests);

        $batchResult = [];

        foreach ($transport->batchSend($batchOptions) as $key => $result) {
            $request = $requests[$key];
            $batchResult[$key] = $request->newResult($result);
        }

        return $batchResult;
    }
}
