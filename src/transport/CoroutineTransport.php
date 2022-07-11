<?php

declare(strict_types=1);

namespace Verdient\http\transport;

use Verdient\http\Request;
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use Verdient\http\exception\ConnectionRefusedException;
use Verdient\http\exception\ConnectionResetException;
use Verdient\http\exception\HttpException;
use Verdient\http\exception\TimeoutException;
use function Swoole\Coroutine\run as coroutineRun;
use function Swoole\Coroutine\batch as batchCoroutine;

/**
 * 协程传输
 * @author Verdient。
 */
class CoroutineTransport extends AbstractTransport
{
    /**
     * 准备
     * @param Request $request 请求对象
     * @return array
     * @author Verdient。
     */
    protected function prepare(Request $request)
    {
        $options = [];
        $url = parse_url($request->getUrl());
        $options['https'] = $url['scheme'] === 'https';
        if (isset($url['port'])) {
            $options['port'] = $url['port'];
        } else {
            $options['port'] = $options['https'] ? 443 : 80;
        }
        $options['path'] = isset($url['path']) ? $url['path'] : '/';
        if (isset($url['query'])) {
            $options['path'] .= '?' . $url['query'];
        }
        $options['headers'] = $request->getHeaders();
        $options['content'] = $request->getContent();
        $options['method'] = $request->getMethod();
        $options['timeout'] = $request->getTimeout();
        $options['host'] = $url['host'];
        $options['headers']['Host'] = $url['host'];
        if ($request->getProxyHost()) {
            $options['proxyHost'] = $request->getProxyHost();
        }
        if ($request->getProxyPort()) {
            $options['proxyPort'] = $request->getProxyPort();
        }
        return $options;
    }

    /**
     * 获取状态信息
     * @param int $code 状态码
     * @return string
     * @author Verdient。
     */
    protected function getStatusMessage($code)
    {
        $map = [
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => '(Unused)',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported'
        ];
        return isset($map[$code]) ? $map[$code] : '';
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function send(Request $request)
    {
        $status = '';
        $headers = [];
        $content = null;
        $response = null;
        if (Coroutine::getPcid() === false) {
            coroutineRun(function () use ($request, &$status, &$headers, &$content, &$response) {
                list($status, $headers, $content, $response) = $this->request($this->prepare($request));
            });
        } else {
            list($status, $headers, $content, $response) = $this->request($this->prepare($request));
        }
        return [$status, $headers, $content, $response];
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function batchSend(array $requests)
    {
        $responses = [];
        if (Coroutine::getPcid() === false) {
            coroutineRun(function () use ($requests, &$responses) {
                foreach ($requests as $key => $request) {
                    Coroutine::create(function () use ($request, $key, &$responses) {
                        list($status, $headers, $content, $response) = $this->request($this->prepare($request));
                        $responses[$key] = [$status, $headers, $content, $response];
                    });
                }
            });
        } else {
            $tasks = [];
            foreach ($requests as $key => $request) {
                $tasks[$key] = function () use ($request) {
                    return $this->request($this->prepare($request));
                };
            }
            $responses = batchCoroutine($tasks);
        }
        $result = [];
        foreach ($requests as $key => $requests) {
            $result[$key] = $responses[$key];
        }
        unset($responses);
        return $result;
    }

    /**
     * 请求
     * @param array $options 请求参数
     * @return array
     * @author Verdient。
     */
    protected function request($options)
    {
        $statusCode = 0;
        $headers = [];
        $content = null;
        $client = new Client($options['host'], $options['port'], $options['https']);
        $client->setHeaders($options['headers']);
        $client->setData($options['content']);
        $client->setMethod($options['method']);
        $sets = [];
        foreach (['proxyHost' => 'http_proxy_host', 'proxyPort' => 'http_proxy_port', 'timeout' => 'timeout'] as $option => $config) {
            if (isset($options[$option])) {
                $sets[$config] = $options[$option];
            }
        }
        $client->set($sets);
        $client->execute($options['path']);
        if ($client->errCode !== 0) {
            $errorMessage = '[' . $client->errCode . '] ' . socket_strerror($client->errCode);
            switch ($client->statusCode) {
                case -1:
                    throw new ConnectionRefusedException($errorMessage, $client->errCode);
                case -2:
                    throw new TimeoutException($errorMessage, $client->errCode);
                case -3:
                    throw new ConnectionResetException($errorMessage, $client->errCode);
                default:
                    throw new HttpException($errorMessage, $client->errCode);
            }
        }
        $statusCode = $client->statusCode;
        $cookies = $client->set_cookie_headers;
        if (is_array($cookies)) {
            foreach ($cookies as $value) {
                $headers[] = 'Set-Cookie: ' . $value;
            }
        }
        foreach ($client->getHeaders() as $name => $value) {
            if (strtolower($name) !== 'set-cookie') {
                $headers[] = ucwords($name, '-') . ': ' . $value;
            }
        }
        $headers = implode("\r\n", $headers);
        $content = $client->getBody();
        $client->close();
        $status = 'HTTP/1.1 ' . $statusCode . ' ' . $this->getStatusMessage($statusCode);
        $response = $status . "\r\n" . $headers . "\r\n\r\n" . $content;
        return [$status, $headers, $content, $response];
    }
}
