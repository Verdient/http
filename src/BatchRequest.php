<?php
namespace Verdient\http;

use chorus\InvalidConfigException;
use chorus\ObjectHelper;
use Verdient\http\transport\TransportInterface;

/**
 * 批量请求
 * @author Verdient。
 */
class BatchRequest extends \chorus\BaseObject
{
	/**
	 * @var array 内建传输通道
	 * @author Verdient。
	 */
	const BUILT_IN_TRANSPORTS = [
		'cUrl' => 'Verdient\http\transport\CUrlTransport',
		'coroutine' => 'Verdient\http\transport\CoroutineTransport',
		'stream' => 'Verdient\http\transport\StreamTransport'
	];

	/**
	 * 批大小
	 * @author Verdient。
	 */
	public $batchSize = 100;

	/**
	 * @var string 传输通道
	 * @author Verdient。
	 */
	public $transport = 'cUrl';

	/**
	 * @var array 传输通道
	 * @author Verdient。
	 */
	public $transports = [];

	/**
	 * @var array 请求集合
	 * @author Verdient。
	 */
	protected $requests = [];

	/**
	 * @var TransportInterface 传输通道
	 * @author Verdient。
	 */
	protected $_transport = null;

	/**
	 * @inheritdoc
	 * @author Verdient。
	 */
	public function init(){
		parent::init();
		$this->transports = array_merge(static::BUILT_IN_TRANSPORTS, $this->transports);
	}

	/**
	 * 设置请求
	 * @param array $requests 请求集合
	 * @param int $batchSize 批大小
	 * @return BatchRequest
	 * @author Verdient。
	 */
	public function setRequests($requests, $batchSize = null){
		if(!$batchSize){
			$batchSize = $this->batchSize;
		}
		$this->requests = array_chunk($requests, $batchSize, true);
		return $this;
	}

	/**
	 * 获取传输通道
	 * @param $name 通道名称
	 * @return TransportInterface
	 * @author Verdient。
	 */
	public function getTransport(){
		if($this->_transport === null){
			if(!isset($this->transports[$this->transport])){
				throw new InvalidConfigException('Unkrown transport: ' . $this->transport);
			}
			$transport = ObjectHelper::create($this->transports[$this->transport]);
			if(!$transport instanceof TransportInterface){
				throw new InvalidConfigException('transport must instance of ' . TransportInterface::class);
			}
			$this->_transport = $transport;
		}
		return $this->_transport;
	}

	/**
	 * 发送请求
	 * @return array
	 */
	public function send(){
		$responses = [];
		foreach($this->requests as $requests){
			foreach($requests as $request){
				$request->trigger(Request::EVENT_BEFORE_REQUEST, $request);
				$request->prepare();
			}
			foreach($this->getTransport()->batchSend($requests) as $key => $result){
				list($statusCode, $headers, $content, $response) = $result;
				$request = $requests[$key];
				$request->trigger(Request::EVENT_AFTER_REQUEST);
				$responses[$key] = ObjectHelper::create([
					'class' => $request::responseClass(),
					'request' => $request,
					'tryParse' => $request->tryParse,
					'parsers' => $request->parsers
				], $statusCode, $headers, $content, $response);
			}
		}
		return $responses;
	}
}