<?php

declare(strict_types=1);

namespace Verdient\http\transport;

use Verdient\http\exception\HttpException;
use Verdient\http\Request;

/**
 * Stream传输
 * @author Verdient。
 */
class StreamTransport extends AbstractTransport
{
    /**
     * @var array 默认参数
     * @author Verdient。
     */
    const DEFAULT_OPTIONS = [
        'http' => [
            'ignore_errors' => true
        ],
        'ssl' => [
            'verify_peer' => false
        ]
    ];

    /**
     * 准备
     * @param Request $request 请求对象
     * @return array
     * @author Verdient。
     */
    protected function prepare(Request $request)
    {
        $options = static::DEFAULT_OPTIONS;
        $options['http']['method'] = strtoupper($request->getMethod());
        if (!empty($request->getHeaders())) {
            $headers = [];
            foreach ($request->getHeaders() as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $element) {
                        $headers[] = $key . ':' . $element;
                    }
                } else {
                    $headers[] = $key . ':' . $value;
                }
            }
            $options['http']['header'] = $headers;
        }
        $options['http']['content'] = $request->getContent();
        $options['http']['timeout'] = $request->getTimeout();
        if ($request->getProxyHost()) {
            $options['http']['proxy'] = 'tcp://' . $request->getProxyHost();
        }
        if ($request->getProxyPort()) {
            $options['http']['proxy'] .= ':' . $request->getProxyPort();
        }
        return $options;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function send(Request $request)
    {
        $options = $this->prepare($request);
        $context = stream_context_create($options);
        if (!$stream = @fopen($request->getUrl(), 'rb', false, $context)) {
            $error = error_get_last();
            throw new HttpException($error['message'] ?? json_encode($error));
        }
        $content = stream_get_contents($stream);
        $rawHeaders = (array) $http_response_header;
        $response = implode("\r\n", $rawHeaders) . "\r\n\r\n" . $content;
        $status = array_shift($rawHeaders);
        $headers = implode("\r\n", $rawHeaders);
        fclose($stream);
        return [$status, $headers, $content, $response];
    }
}
