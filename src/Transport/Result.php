<?php

declare(strict_types=1);

namespace Verdient\Http\Transport;

/**
 * 结果类
 *
 * 此类用于表示 HTTP 请求的结果，包括请求的状态、头部信息、内容等。
 *
 * @author Verdient
 */
readonly class Result
{
    /**
     * 构造函数
     *
     * @param bool $isOK 请求是否成功
     * @param ?string $status HTTP 状态行
     * @param ?string $headers 响应头部信息
     * @param ?string $content 响应内容
     * @param ?string $response 完整的响应信息
     * @param ?int $errorCode 错误代码（如果有的话）
     * @param ?string $errorMessage 错误信息（如果有的话）
     * @author Verdient
     */
    public function __construct(
        public bool $isOK,
        public ?string $status = null,
        public ?string $headers = null,
        public ?string $content = null,
        public ?string $response = null,
        public ?int $errorCode = null,
        public ?string $errorMessage = null
    ) {}
}
