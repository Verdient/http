<?php

declare(strict_types=1);

namespace Verdient\Http\Transport;

use Verdient\Http\Options;

/**
 * 传输通道接口
 *
 * @author Verdient。
 */
interface TransportInterface
{
    /**
     * 发送
     *
     * @param Options $options 请求选项
     * @author Verdient。
     */
    public function send(Options $options): Result;

    /**
     * 批量发送
     *
     * @param Options[] $batchOptions 包含请求选项的数组
     * @return Result[]
     * @author Verdient。
     */
    public function batchSend(array $batchOptions): array;
}
