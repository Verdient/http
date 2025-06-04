<?php

declare(strict_types=1);

namespace Verdient\Http;

/**
 * 响应接口
 *
 * @author Verdient。
 */
interface ResponseInterface
{
    /**
     * 获取请求对象
     *
     * @author Verdient。
     */
    public function getRequest(): Request;

    /**
     * 获取原始响应
     *
     * @author Verdient。
     */
    public function getRawResponse(): string;

    /**
     * 获取原始状态行
     *
     * @author Verdient。
     */
    public function getRawStatus(): string;

    /**
     * 获取原始头部
     *
     * @author Verdient。
     */
    public function getRawHeaders(): string;

    /**
     * 获取原始消息体
     *
     * @author Verdient。
     */
    public function getRawContent(): ?string;


    /**
     * 获取HTTP版本
     *
     * @author Verdient。
     */
    public function getHttpVersion(): string;

    /**
     * 获取状态码
     *
     * @author Verdient。
     */
    public function getStatusCode(): int;

    /**
     * 获取状态消息
     *
     * @author Verdient。
     */
    public function getStatusMessage(): string;

    /**
     * 获取头部
     *
     * @author Verdient。
     */
    public function getHeaders(): array;

    /**
     * 获取消息体
     *
     * @author Verdient。
     */
    public function getBodies(): mixed;

    /**
     * 获取消息体类型
     *
     * @author Verdient。
     */
    public function getContentType(): ?string;

    /**
     * 获取字符集
     *
     * @author Verdient。
     */
    public function getCharset(): ?string;

    /**
     * 获取 Cookies
     *
     * @author Verdient。
     */
    public function getCookies(): array;
}
