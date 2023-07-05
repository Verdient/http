<?php

declare(strict_types=1);

namespace Verdient\http\transport;

use Verdient\http\Request;

/**
 * 传输通道接口
 * @author Verdient。
 */
interface TransportInterface
{
    /**
     * 发送
     * @param Request $request 请求对象
     * @return array [$statusCode, $headers, $content, $response]
     * @author Verdient。
     */
    public function send(Request $request);

    /**
     * 批量发送
     * @param array $requests 包含请求对象的数组
     * @return array
     * @author Verdient。
     */
    public function batchSend(array $requests);
}
