<?php
namespace Verdient\http;

/**
 * 批量请求
 * @author Verdient。
 */
class BatchRequest
{
	/**
	 * @var array 请求集合
	 * @author Verdient。
	 */
	protected $requests = [];

	/**
	 * @var resource CURL句柄
	 * @author Verdient。
	 */
	protected $_curl = false;

	/**
	 * 注入请求对象
	 * @author Verdient。
	 */
	public function __construct(array $requests){
		$this->requests = $requests;
	}

	/**
	 * 获取CURL句柄
	 * @return resource
	 * @author Verdient。
	 */
	public function getCurl(){
		if($this->_curl === false){
			$this->_curl = curl_multi_init();
		}
		return $this->_curl;
	}

	/**
	 * 准备请求
	 * @author Verdient。
	 */
	public function prepareRequest(){
		$curl = $this->getCurl();
		foreach($this->requests as $request){
			$request->prepareRequest();
			curl_multi_add_handle($curl, $request->getCurl());
		}
	}

	/**
	 * 发送请求
	 * @param bool $raw 是否返回原文
	 * @return array
	 */
	public function send($raw = false){
		$this->prepareRequest();
		$active = null;
		$mh = $this->getCurl();
		do {
			curl_multi_exec($mh, $running);
			curl_multi_select($mh);
		} while ($running > 0);
		$responses = [];
		foreach($this->requests as $key => $request){
			$ch = $request->getCurl();
			$response = curl_multi_getcontent($ch);
			curl_multi_remove_handle($mh, $ch);
			$responses[$key] = $request->prepareResponse($response, $raw);
		}
		return $responses;
	}

	/**
	 * 释放资源
	 * @return Request
	 * @author Verdient。
	*/
	public function releaseResource(){
		if($this->_curl !== null){
			@curl_multi_close($this->_curl);
			$this->_curl = null;
		}
	}

	/**
	 * 析构时释放资源
	 * @author Verdient。
	 */
	public function __destruct(){
		$this->releaseResource();
	}
}