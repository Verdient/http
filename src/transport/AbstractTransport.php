<?php
namespace Verdient\http\transport;

/**
 * 抽象传输通道
 * @author Verdient。
 */
abstract class AbstractTransport implements TransportInterface
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function batchSend(array $requests)
    {
        $responses = [];
        foreach($requests as $key => $request){
            $responses[$key] = $this->send($request);
        }
        return $responses;
    }
}