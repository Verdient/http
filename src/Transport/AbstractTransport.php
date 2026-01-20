<?php

namespace Verdient\Http\Transport;

use Override;

/**
 * 抽象传输通道
 *
 * @author Verdient。
 */
abstract class AbstractTransport implements TransportInterface
{
    /**
     * @author Verdient。
     */
    #[Override]
    public function batchSend(array $batchOptions): array
    {
        $result = [];

        foreach ($batchOptions as $key => $options) {
            $result[$key] = $this->send($options);
        }

        return $result;
    }
}
