<?php

declare(strict_types=1);

namespace Verdient\Http;

/**
 * 选项
 *
 * @author Verdient。
 */
readonly class Options
{
    /**
     * @param string $url URL
     * @param string $method 请求方法
     * @param array $headers 头部参数
     * @param ?string $content 消息体
     * @param ?int $timeout 超时时间
     * @param ?string $proxyHost 代理主机
     * @param ?int $proxyPort 代理端口
     * @author Verdient。
     */
    public function __construct(
        public string $url,
        public string $method,
        public array $headers,
        public ?string $content,
        public ?int $timeout,
        public ?string $proxyHost = null,
        public ?int $proxyPort = null,
    ) {}
}
