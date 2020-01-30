<?php
namespace http\builder;

/**
 * BuilderInterface
 * 构建器接口
 * ----------------
 * @author Verdient。
 */
interface BuilderInterface
{
	/**
	 * getElements()
	 * 获取元素
	 * -------------
	 * @return Array
	 * @author Verdient。
	 */
	public function getElements();

	/**
	 * setElements(Array $elements)
	 * 设置元素
	 * ----------------------------
	 * @param Array $elements 元素
	 * --------------------------
	 * @return Array
	 * @author Verdient。
	 */
	public function setElements($elements);

	/**
	 * addElement(String $name, String $value)
	 * 添加元素
	 * ---------------------------------------
	 * @param String $name 名称
	 * @param String $value 内容
	 * ------------------------
	 * @return FormData
	 * @author Verdient。
	 */
	public function addElement($name, $value);

	/**
	 * removeElement(String $name)
	 * 移除元素
	 * ---------------------------
	 * @param String $name 名称
	 * ------------------------
	 * @return FormData
	 * @author Verdient。
	 */
	public function removeElement($name);

	/**
	 * toString()
	 * 转为字符串
	 * ----------
	 * @return String
	 * @author Verdient。
	 */
	public function toString();

	/**
	 * headers()
	 * 附加的头部
	 * ---------
	 * @return Array
	 * @author Verdient。
	 */
	public function headers();
}