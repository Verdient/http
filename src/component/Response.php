<?php
namespace Verdient\http\component;

use chorus\ArrayHelper;

/**
 * Response
 * 响应
 * --------
 * @author Verdient。
 */
class Response extends \Verdient\http\Response
{
	/**
	 * getIsSuccess()
	 * 获取是否成功
	 * --------------
	 * @return Boolean
	 * @author Verdient。
	 */
	public function getIsSuccess(){
		$statusCode = $this->getStatusCode();
		return $statusCode >= 200 && $statusCode < 300;
	}

	/**
	 * hasError()
	 * 是否有错误
	 * ----------
	 * @inheritdoc
	 * -----------
	 * @author Verdient。
	 */
	public function hasError(){
		return !$this->getIsSuccess();
	}

	/**
	 * getError()
	 * 获取错误
	 * ----------
	 * @inheritdoc
	 * -----------
	 * @return Array|Null
	 * @author Verdient。
	 */
	public function getError(){
		if($this->hasError()){
			if($error = parent::getError()){
				return $error;
			}
			return $this->getBody();
		}
		return null;
	}

	/**
	 * getErrorMessage()
	 * 获取错误信息
	 * -----------------
	 * @inheritdoc
	 * -----------
	 * @return String
	 * @author Verdient。
	 */
	public function getErrorMessage(){
		if($error = $this->getError()){
			if(ArrayHelper::isIndexed($error) && isset($error[0])){
				$error = $error[0];
			}
			if(isset($error['message'])){
				return $error['message'];
			}
		}
		return null;
	}
}