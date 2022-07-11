<?php

declare(strict_types=1);

namespace Verdient\http\transport;

use Verdient\http\exception\ConnectionRefusedException;
use Verdient\http\exception\HttpException;
use Verdient\http\exception\TimeoutException;
use Verdient\http\Request;

/**
 * CURL传输
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
     * 准备
     * @param Request $request 请求对象
     * @return array
     * @author Verdient。
     */
    protected function prepare(Request $request)
    {
        $options = static::DEFAULT_OPTIONS;
        $options[CURLOPT_URL] = $request->getUrl();
        $method = strtoupper($request->getMethod());
        if ($method === 'HEAD') {
            $options[CURLOPT_NOBODY] = true;
            unset($options[CURLOPT_WRITEFUNCTION]);
        }
        if (!in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            unset($options[CURLOPT_POSTFIELDS]);
            unset($options[CURLOPT_POST]);
        }
        $options[CURLOPT_CUSTOMREQUEST] = $method;
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
            $options[CURLOPT_HTTPHEADER] = $headers;
        }
        if (!empty($request->getContent())) {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $request->getContent();
        }
        if ($request->getProxyHost()) {
            $options[CURLOPT_PROXY] = $request->getProxyHost();
        }
        if ($request->getProxyPort()) {
            $options[CURLOPT_PROXYPORT] = $request->getProxyPort();
        }
        $options[CURLOPT_TIMEOUT] = $request->getTimeout();
        $options[CURLOPT_CONNECTTIMEOUT] = $request->getTimeout();
        return $options;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function send(Request $request)
    {
        $options = $this->prepare($request);
        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        if ($response === false) {
            $errorNo = curl_errno($curl);
            $errorMessage = '[' . $errorNo . '] ' . curl_error($curl) ?: curl_strerror($errorNo);
            curl_close($curl);
            switch ($errorNo) {
                case CURLE_COULDNT_CONNECT:
                    throw new ConnectionRefusedException($errorMessage, $errorNo);
                case CURLE_OPERATION_TIMEOUTED:
                    throw new TimeoutException($errorMessage, $errorNo);
                default:
                    throw new HttpException($errorMessage, $errorNo);
            }
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
        return [$status, $headers, $content, $response];
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function batchSend(array $requests)
    {
        $resources = [];
        $mh = curl_multi_init();
        foreach ($requests as $key => $request) {
            $resource = curl_init();
            $options = $this->prepare($request);
            curl_setopt_array($resource, $options);
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
            $responses[$key] = [$status, $headers, $content, $response];
        }
        curl_multi_close($mh);
        return $responses;
    }
}
