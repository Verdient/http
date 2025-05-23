<?php

declare(strict_types=1);

namespace Verdient\Http;

/**
 * 结果类
 *
 * 此类用于表示 HTTP 请求的结果，包括请求的状态、头部信息、响应体等。
 *
 * @author Verdient
 */
readonly class Result
{
    /**
     * 构造函数
     *
     * @param bool $isOK 请求是否成功
     * @param ?ResponseInterface $response 完整的响应对象
     * @param ?int $errorCode 错误代码（如果有的话）
     * @param ?string $errorMessage 错误信息（如果有的话）
     * @author Verdient
     */
    public function __construct(
        protected bool $isOK,
        protected ?ResponseInterface $response = null,
        protected ?int $errorCode = null,
        protected ?string $errorMessage = null
    ) {}

    /**
     * 获取是否成功
     *
     * @author Verdient。
     */
    public function getIsOK(): bool
    {
        return $this->isOK;
    }

    /**
     * 获取请求对象
     *
     * @author Verdient。
     */
    public function getRequest(): ?Request
    {
        return $this->response ? $this->response->getRequest() : null;
    }

    /**
     * 获取响应对象
     *
     * @author Verdient。
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    /**
     * 获取错误码
     *
     * @author Verdient。
     */
    public function getErrorCode(): ?int
    {
        return $this->errorCode;
    }

    /**
     * 获取错误信息
     *
     * @author Verdient。
     */
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * 获取状态码
     *
     * @author Verdient。
     */
    public function getStatusCode(): ?int
    {
        return $this->response ? $this->response->getStatusCode() : null;
    }

    /**
     * 获取头部参数
     *
     * @author Verdient。
     */
    public function getHeaders(): ?array
    {
        return $this->response ? $this->response->getHeaders() : null;
    }

    /**
     * 获取消息体参数
     *
     * @author Verdient。
     */
    public function getBodies(): mixed
    {
        return $this->response ? $this->response->getBodies() : null;
    }
}
