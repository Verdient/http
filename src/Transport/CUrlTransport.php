<?php

declare(strict_types=1);

namespace Verdient\Http\Transport;

use Verdient\Http\Exception\HttpException;
use Verdient\Http\Options;

/**
 * CURL传输
 *
 * @author Verdient。
 */
class CUrlTransport extends AbstractTransport
{
    /**
     * @var array 默认参数
     * @author Verdient。
     */
    const DEFAULT_OPTIONS = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER => []
    ];

    /**
     * 解决选项
     *
     * @param Options $options 请求对象
     * @author Verdient。
     */
    protected function resolveOptions(Options $options): array
    {
        $result = static::DEFAULT_OPTIONS;

        $result[CURLOPT_URL] = $options->url;

        $method = $options->method;

        if ($method === 'HEAD') {
            $result[CURLOPT_NOBODY] = true;
            unset($result[CURLOPT_WRITEFUNCTION]);
        }

        if (!in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            unset($result[CURLOPT_POSTFIELDS]);
            unset($result[CURLOPT_POST]);
        }

        $result[CURLOPT_CUSTOMREQUEST] = $method;

        if (!empty($options->headers)) {
            $headers = [];
            foreach ($options->headers as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $element) {
                        $headers[] = $key . ':' . $element;
                    }
                } else {
                    $headers[] = $key . ':' . $value;
                }
            }
            $result[CURLOPT_HTTPHEADER] = $headers;
        }

        if (!empty($options->content)) {
            $result[CURLOPT_POST] = true;
            $result[CURLOPT_POSTFIELDS] = $options->content;
        }

        if ($options->proxyHost) {
            $result[CURLOPT_PROXY] = $options->proxyHost;
        }

        if ($options->proxyPort) {
            $result[CURLOPT_PROXYPORT] = $options->proxyPort;
        }

        $result[CURLOPT_TIMEOUT] = $options->timeout;
        $result[CURLOPT_CONNECTTIMEOUT] = $options->timeout;

        return $result;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function send(Options $options): Result
    {
        $options = $this->resolveOptions($options);

        $curl = curl_init();

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);

        if ($response === false) {
            $errorNo = curl_errno($curl);

            return new Result(
                isOK: false,
                errorCode: $errorNo,
                errorMessage: curl_strerror($errorNo)
            );
        }

        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);

        $content = mb_substr($response, $headerSize);
        $headers = mb_substr($response, 0, $headerSize - 4);

        $status = '';

        $position = strrpos($headers, "\r\n\r\n");

        if ($position !== false) {
            $status = mb_substr($headers, 0, $position + 4);
            $headers = mb_substr($headers, $position + 4);
        }

        $position = strpos($headers, "\r\n");

        $status .= mb_substr($headers, 0, $position);
        $headers = mb_substr($headers, $position + 2);

        curl_close($curl);

        return new Result(
            isOK: true,
            status: $status,
            headers: $headers,
            content: $content,
            response: $response
        );
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function batchSend(array $batchOptions): array
    {
        $resources = [];
        $mh = curl_multi_init();

        foreach ($batchOptions as $key => $options) {
            $resource = curl_init();
            $resolvedOptions = $this->resolveOptions($options);
            curl_setopt_array($resource, $resolvedOptions);
            $resources[$key] = $resource;
            curl_multi_add_handle($mh, $resource);
        }

        try {
            $running = null;
            do {
                if (curl_multi_select($mh) === -1) {
                    usleep(100);
                }
                do {
                    $code = curl_multi_exec($mh, $running);
                } while ($code === CURLM_CALL_MULTI_PERFORM);
            } while ($running > 0 && $code === CURLM_OK);
        } catch (\Throwable $e) {
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }

        $responses = [];

        foreach ($resources as $key => $resource) {
            $errorNo = curl_errno($resource);

            if ($errorNo !== 0) {
                $responses[$key] = new Result(
                    isOK: false,
                    errorCode: $errorNo,
                    errorMessage: curl_strerror($errorNo),
                    response: ''
                );
            } else {
                $response = curl_multi_getcontent($resource);
                curl_multi_remove_handle($mh, $resource);

                $headerSize = curl_getinfo($resource, CURLINFO_HEADER_SIZE);
                $content = mb_substr($response, $headerSize);
                $headers = mb_substr($response, 0, $headerSize - 4);

                $status = '';
                $position = strrpos($headers, "\r\n\r\n");
                if ($position !== false) {
                    $status = mb_substr($headers, 0, $position + 4);
                    $headers = mb_substr($headers, $position + 4);
                }

                $position = strpos($headers, "\r\n");
                $status .= mb_substr($headers, 0, $position);
                $headers = mb_substr($headers, $position + 2);

                $responses[$key] = new Result(
                    isOK: true,
                    status: $status,
                    headers: $headers,
                    content: $content,
                    response: $response
                );
            }
        }

        curl_multi_close($mh);

        return $responses;
    }
}
