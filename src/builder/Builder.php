<?php
namespace Verdient\http\builder;

/**
 * 构建器
 * @author Verdient。
 */
abstract class Builder extends \chorus\BaseObject implements BuilderInterface
{
	/**
	 * @var string 字符集
	 * @author Verdient。
	 */
	public $charset = null;

	/**
	 * @var string 消息体类型
	 * @author Verdient。
	 */
	public $contentType = null;

	/**
	 * @var array 元素内容
	 * @author Verdient。
	 */
	protected $_elements = [];

	/**
	 * @inheritdoc
	 * @author Verdient。
	 */
	public function getElements(){
		return $this->_elements;
	}

	/**
	 * @inheritdoc
	 * @author Verdient。
	 */
	public function setElements($elements){
		$this->_elements = $elements;
		return $this;
	}

	/**
	 * @inheritdoc
	 * @author Verdient。
	 */
	public function addElement($name, $value){
		$this->_elements[$name] = $value;
		return $this;
	}

	/**
	 * @inheritdoc
	 * @author Verdient。
	 */
	public function removeElement($name){
		unset($this->_elements[$name]);
		return $this;
	}

	/**
	 * @inheritdoc
	 * @author Verdient。
	 */
	public function headers(){
		if(!empty($this->contentType)){
			$contentType = $this->contentType;
			if(!empty($this->charset)){
				$contentType .= '; charset=' . $this->charset;
			}
			return [
				'Content-Type' => $contentType
			];
		}
	}
}