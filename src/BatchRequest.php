<?php
namespace Verdient\http;

use chorus\UnsatisfiedExcepiton;

/**
 * 批量请求
 * @author Verdient。
 */
class BatchRequest
{
	/**
	 * 批大小
	 * @author Verdient。
	 */
	public $batchSize = 1000;

	/**
	 * @var array 请求集合
	 * @author Verdient。
	 */
	protected $requests = [];

	/**
	 * 注入请求对象
	 * @author Verdient。
	 */
	public function __construct(array $requests){
		$this->requests = array_chunk($requests, $this->batchSize, true);
	}

	/**
	 * 发送请求
	 * @param bool $raw 是否返回原文
	 * @return array
	 */
	public function send($raw = false){
		$responses = [];
		$mh = curl_multi_init();
		foreach($this->requests as $requests){
			foreach($requests as $request){
				$request->prepareRequest();
				curl_multi_add_handle($mh, $request->getCurl());
			}
			do{
				curl_multi_exec($mh, $running);
				curl_multi_select($mh);
			}while($running > 0);
			$responses = [];
			foreach($requests as $key => $request){
				$ch = $request->getCurl();
				$response = curl_multi_getcontent($ch);
				curl_multi_remove_handle($mh, $ch);
				$responses[$key] = $request->prepareResponse($response, $raw);
			}
		}
		@curl_multi_close($mh);
		return $responses;
	}
}