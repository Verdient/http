<?php

declare(strict_types=1);

namespace Verdient\Http\Transport;

use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use Verdient\Http\Options;

use function Swoole\Coroutine\run as coroutineRun;
use function Swoole\Coroutine\batch as batchCoroutine;

/**
 * 协程传输
 * @author Verdient。
 */
class CoroutineTransport extends AbstractTransport
{
    /**
     * 解决选项
     *
     * @param Options $options 请求选项
     * @author Verdient。
     */
    protected function resolveOptions(Options $options): array
    {
        $result = [];

        $url = parse_url($options->url);

        $result['https'] = $url['scheme'] === 'https';

        if (isset($url['port'])) {
            $result['port'] = $url['port'];
        } else {
            $result['port'] = $result['https'] ? 443 : 80;
        }

        $result['path'] = isset($url['path']) ? $url['path'] : '/';

        if (isset($url['query'])) {
            $result['path'] .= '?' . $url['query'];
        }

        $result['headers'] = $options->headers;
        $result['content'] = $options->content;
        $result['method'] = $options->method;
        $result['timeout'] = $options->timeout;
        $result['host'] = $url['host'];

        $host = $url['host'];

        if (!empty($url['port'])) {
            $host .= ':' . $url['port'];
        }

        $result['headers']['Host'] = $host;

        if ($options->proxyHost) {
            $result['proxyHost'] = $options->proxyHost;
        }

        if ($options->proxyPort) {
            $result['proxyPort'] = $options->proxyPort;
        }

        return $result;
    }

    /**
     * 获取状态信息
     *
     * @param int $code 状态码
     * @author Verdient。
     */
    protected function getStatusMessage(int $code): string
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
    public function send(Options $options): Result
    {
        $result = null;
        if (Coroutine::getPcid() === false) {
            coroutineRun(function () use ($options, &$result) {
                $result = $this->request($this->resolveOptions($options));
            });
        } else {
            $result = $this->request($this->resolveOptions($options));
        }

        return $result;
    }

    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function batchSend(array $batchOptions): array
    {
        $responses = [];
        if (Coroutine::getPcid() === false) {
            coroutineRun(function () use ($batchOptions, &$responses) {
                foreach ($batchOptions as $key => $options) {
                    Coroutine::create(function () use ($options, $key, &$responses) {
                        $result = $this->request($this->resolveOptions($options));
                        $responses[$key] = $result;
                    });
                }
            });
        } else {
            $tasks = [];
            foreach ($batchOptions as $key => $options) {
                $tasks[$key] = function () use ($options) {
                    return $this->request($this->resolveOptions($options));
                };
            }
            $responses = batchCoroutine($tasks);
        }
        $result = [];
        foreach (array_keys($batchOptions) as $key) {
            $result[$key] = $responses[$key];
        }
        unset($responses);
        return $result;
    }

    /**
     * 请求
     *
     * @param array $options 请求参数
     * @author Verdient。
     */
    protected function request(array $options): Result
    {
        $statusCode = 0;

        $headers = [];

        $content = null;

        $client = new Client($options['host'], $options['port'], $options['https']);

        $client->setHeaders($options['headers']);
        $client->setData($options['content']);
        $client->setMethod($options['method']);

        $sets = [];

        foreach (
            [
                'proxyHost' => 'http_proxy_host',
                'proxyPort' => 'http_proxy_port',
                'timeout' => 'timeout'
            ] as $option => $config
        ) {
            if (isset($options[$option])) {
                $sets[$config] = $options[$option];
            }
        }

        $client->set($sets);

        $client->execute($options['path']);

        if ($client->errCode !== 0) {
            return new Result(
                isOK: false,
                errorCode: $client->errCode,
                errorMessage: socket_strerror($client->errCode)
            );
        }

        $statusCode = $client->statusCode;

        $cookies = $client->set_cookie_headers;

        if (is_array($cookies)) {
            foreach ($cookies as $value) {
                $headers[] = 'Set-Cookie: ' . $value;
            }
        }

        foreach ($client->getHeaders() as $name => $value) {
            if (strtolower($name) !== 'set-cookie' && is_string($value)) {
                if (is_array($value)) {
                    foreach ($value as $singleValue) {
                        $headers[] = ucwords($name, '-') . ': ' . $singleValue;
                    }
                } else {
                    $headers[] = ucwords($name, '-') . ': ' . $value;
                }
            }
        }

        $headers = implode("\r\n", $headers);

        $content = $client->getBody();

        $client->close();

        $status = 'HTTP/1.1 ' . $statusCode . ' ' . $this->getStatusMessage($statusCode);

        $response = $status . "\r\n" . $headers . "\r\n\r\n" . $content;

        return new Result(
            isOK: true,
            status: $status,
            headers: $headers,
            content: $content,
            response: $response
        );
    }
}
