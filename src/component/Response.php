<?php
namespace Verdient\http\component;

/**
 * 响应
 * @author Verdient。
 */
class Response extends \Verdient\http\Response
{
	/**
	 * 获取是否成功
	 * @return bool
	 * @author Verdient。
	 */
	public function getIsSuccess(){
		$statusCode = $this->getStatusCode();
		return $statusCode >= 200 && $statusCode < 300;
	}

	/**
	 * 是否有错误
	 * @return bool
	 * @author Verdient。
	 */
	public function hasError(){
		return !$this->getIsSuccess();
	}

	/**
	 * 获取错误
	 * @return array|null
	 * @author Verdient。
	 */
	public function getError(){
		if($this->hasError()){
			return $this->getBody();
		}
		return null;
	}

	/**
	 * 获取错误信息
	 * @return string
	 * @author Verdient。
	 */
	public function getErrorMessage(){
		if($error = $this->getError()){
			if(isset($error['message'])){
				return $error['message'];
			}
			return (string) $error;
		}
		return null;
	}
}