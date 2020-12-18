<?php
namespace Verdient\http\transport;

use Verdient\http\Request;

/**
 * CURL
 * @author Verdient。
 */
class CUrlTransport extends Transport
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
	protected function prepare(Request $request){
		$options = static::DEFAULT_OPTIONS;
		$options[CURLOPT_URL] = $request->getUrl();
		$method = strtoupper($request->getMethod());
		if($method === 'HEAD'){
			$options[CURLOPT_NOBODY] = true;
			unset($options[CURLOPT_WRITEFUNCTION]);
		}
		if(!in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])){
			unset($options[CURLOPT_POSTFIELDS]);
			unset($options[CURLOPT_POST]);
		}
		$options[CURLOPT_CUSTOMREQUEST] = $method;
		if(!empty($request->getHeaders())){
			$headers = [];
			foreach($request->getHeaders() as $key => $value){
				if(is_array($value)){
					foreach($value as $element){
						$headers[] = $key . ':' . $element;
					}
				}else{
					$headers[] = $key . ':' . $value;
				}
			}
			$options[CURLOPT_HTTPHEADER] = $headers;
		}
		if(!empty($request->getContent())){
			$options[CURLOPT_POST] = true;
			$options[CURLOPT_POSTFIELDS] = $request->getContent();
		}
		if($request->getProxyHost()){
			$options[CURLOPT_PROXY] = $request->getProxyHost();
		}
		if($request->getProxyPort()){
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
	public function send(Request $request){
		$options = $this->prepare($request);
		$curl = curl_init();
		curl_setopt_array($curl, $options);
		$response = curl_exec($curl);
		if($response === false){
			$error = curl_error($curl) ?: curl_strerror(curl_errno($curl));
			curl_close($curl);
			throw new \Exception($error);
		}
		$headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
		$headers = mb_substr($response, 0, $headerSize - 4);
		$content = mb_substr($response, $headerSize);
		$headers = explode("\r\n", $headers);
		$status = array_shift($headers);
		$headers = implode("\r\n", $headers);
		curl_close($curl);
		return [$status, $headers, $content, $response];
	}

	/**
	 * @inheritdoc
	 * @author Verdient。
	 */
	public function batchSend(array $requests){
		$resources = [];
		$mh = curl_multi_init();
		foreach($requests as $key => $request){
			$resource = curl_init();
			$options = $this->prepare($request);
			curl_setopt_array($resource, $options);
			$resources[$key] = $resource;
			curl_multi_add_handle($mh, $resource);
		}
		try{
			$running = null;
			do{
				if(curl_multi_select($mh) === -1) {
					usleep(100);
				}
				do{
					$code = curl_multi_exec($mh, $running);
				}while($code === CURLM_CALL_MULTI_PERFORM);
			}while($running > 0 && $code === CURLM_OK);
		}catch(\Exception $e){
			throw new \Exception($e->getMessage(), $e->getCode(), $e);
		}
		$responses = [];
		foreach($resources as $key => $resource){
			$response = curl_multi_getcontent($resource);
			curl_multi_remove_handle($mh, $resource);
			$headerSize = curl_getinfo($resource, CURLINFO_HEADER_SIZE);
			$headers = mb_substr($response, 0, $headerSize - 4);
			$content = mb_substr($response, $headerSize);
			$headers = explode("\r\n", $headers);
			$status = array_shift($headers);
			$headers = implode("\r\n", $headers);
			$responses[$key] = [$status, $headers, $content, $response];
		}
		curl_multi_close($mh);
		return $responses;
	}
}