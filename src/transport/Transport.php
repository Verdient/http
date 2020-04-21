<?php
namespace Verdient\http\transport;

/**
 * 传输通道
 * @author Verdient。
 */
abstract class Transport implements TransportInterface
{
	/**
	 * @inheritdoc
	 * @author Verdient。
	 */
	public function batchSend(array $requests){
		$responses = [];
		foreach($requests as $key => $request){
			$responses[$key] = $this->send($request);
		}
		return $responses;
	}
}