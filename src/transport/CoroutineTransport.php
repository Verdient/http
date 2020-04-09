<?php
namespace Verdient\http\transport;

use Swoole\Coroutine\Http\Client;
use Verdient\http\Request;

/**
 * 协程传输
 */
class CoroutineTransport extends Transport
{
	/**
	 * 准备
	 * @param Request $request 请求对象
	 * @return array
	 * @author Verdient。
	 */
	protected function prepare(Request $request){
		$request->prepare();
		$options = [];
		$url = parse_url($request->getUrl());
		$options['https'] = $url['scheme'] === 'https';
		if(isset($url['port'])){
			$options['port'] = $url['port'];
		}else{
			$options['port'] = $options['https'] ? 443 : 80;
		}
		$options['path'] = isset($url['path']) ? $url['path'] : '/';
		if(isset($url['query'])){
			$options['path'] .= '?' . $url['query'];
		}
		$options['headers'] = $request->getHeaders();
		$options['content'] = $request->getContent();
		$options['method'] = $request->getMethod();
		$options['host'] = $url['host'];
		$options['headers']['Host'] = $url['host'];
		return $options;
	}

	/**
	 * @inheritdoc
	 * @author Verdient。
	 */
	public function send(Request $request){
		$statusCode = 0;
		$headers = [];
		$content = null;
		$response = null;
		\Swoole\Coroutine\run(function() use ($request, &$statusCode, &$headers, &$content, &$response){
			list($statusCode, $headers, $content, $response) = $this->request($this->prepare($request));
		});
		return [$statusCode, $headers, $content, $response];
	}

	/**
	 * @inheritdoc
	 * @author Verdient。
	 */
	public function batchSend(array $requests){
		$responses = [];
		\Swoole\Coroutine\run(function() use ($requests, &$responses){
			foreach($requests as $key => $request){
				\Swoole\Coroutine::create(function () use ($request, $key, &$responses){
					list($statusCode, $headers, $content, $response) = $this->request($this->prepare($request));
					$responses[$key] = [$statusCode, $headers, $content, $response];
				});
			}
		});
		$result = [];
		foreach($requests as $key => $requests){
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
	protected function request($options){
		$statusCode = 0;
		$headers = [];
		$content = null;
		$client = new Client($options['host'], $options['port'], $options['https']);
		$client->setHeaders($options['headers']);
		$client->setData($options['content']);
		$client->setMethod($options['method']);
		$client->execute($options['path']);
		if($client->errCode !== 0){
			throw new \Exception(socket_strerror($client->errCode));
		}
		$statusCode = $client->statusCode;
		$cookies = $client->set_cookie_headers;
		if(is_array($cookies)){
			foreach($cookies as $value){
				$headers[] = 'Set-Cookie: ' . $value;
			}
		}
		foreach($client->getHeaders() as $name => $value){
			if(strtolower($name) !== 'set-cookie'){
				$headers[] = ucwords($name, '-') . ': ' . $value;
			}
		}
		$headers = implode("\r\n", $headers);
		$content = $client->body;
		$client->close();
		return [$statusCode, $headers, $content, $headers . "\r\n\r\n" . $content];
	}
}