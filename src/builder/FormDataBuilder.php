<?php
namespace Verdient\http\builder;

/**
 * 表单构建器
 * @author Verdient。
 */
class FormDataBuilder extends Builder
{
	/**
	 * @inheritdoc
	 * @author Verdient。
	 */
	public $contentType = 'multipart/form-data';

	/**
	 * @var string 文本类型
	 * @author Verdient。
	 */
	const TEXT = 1;

	/**
	 * @var string 文件类型
	 * @author Verdient。
	 */
	const FILE = 2;

	/**
	 * @var string 分隔符
	 * @author Verdient。
	 */
	protected $_boundary;

	/**
	 * 获取分隔符
	 * @return string
	 * @author Verdient。
	 */
	public function getBoundary(){
		if(!$this->_boundary){
			$this->_boundary = hash('sha256', random_bytes(64));
		}
		return $this->_boundary;
	}


	/**
	 * 批量添加文本
	 * @param array $data 待添加的数据
	 * @return FormDataBuilder
	 * @author Verdient。
	 */
	public function addTexts(Array $data){
		foreach($data as $name => $value){
			$this->addText($name, $value);
		}
		return $this;
	}

	/**
	 * 批量添加文件
	 * @param array $data 待添加的数据
	 * @return FormDataBuilder
	 * @author Verdient。
	 */
	public function addFiles(Array $data){
		foreach($data as $name => $path){
			$this->addFile($name, $path);
		}
		return $this;
	}

	/**
	 * 添加文本
	 * @param string $name 名称
	 * @param string $value 内容
	 * @return FormDataBuilder
	 * @author Verdient。
	 */
	public function addText($name, $value){
		return $this->addElement($name, [static::TEXT, $value]);
	}

	/**
	 * 添加文件
	 * @param string $name 名称
	 * @param string $value 内容
	 * @return FormDataBuilder
	 * @author Verdient。
	 */
	public function addFile($name, $path){
		return $this->addElement($name, [static::FILE, $path]);
	}

	/**
	 * 转换数组键值
	 * @author Verdient。
	 */
	protected function convertArrayKey(&$node, $prefix, &$result) {
		if(!is_array($node)){
			$result[$prefix] = $node;
		}else{
			foreach($node as $key => $value){
				$this->convertArrayKey($value, "{$prefix}[{$key}]", $result);
			}
		}
	}

	/**
	 * @inheritdoc
	 * @author Verdient。
	 */
	public function toString(){
		$boundary = $this->getBoundary();
		$texts = [];
		$files = [];
		foreach($this->getElements() as $name => $value){
			if($value[0] === static::TEXT){
				$texts[$name] = $value[1];
			}else if($value[0] === static::FILE){
				$files[$name] = $value[1];
			}
		}
		$body = [];
		foreach($texts as $key => $value){
			if(!is_array($value)){
				$body_part = "Content-Disposition: form-data; name=\"$key\"\r\n";
				$body_part .= "\r\n$value";
				$body[] = $body_part;
			}else{
				$result = [];
				$this->convertArrayKey($value, $key, $result);
				foreach($result as $k => $v){
					$body_part = "Content-Disposition: form-data; name=\"$k\"\r\n";
					$body_part .= "\r\n$v";
					$body[] = $body_part;
				}
			}
		}
		foreach($files as $key => $value){
			if(!file_exists($value)){
				throw new \Exception('file ' . $value . ' does not exist');
			}
			$type = mime_content_type($value);
			$body_part = "Content-Disposition: form-data; name=\"$key\"; filename=\"{$value}\"\r\n";
			$body_part .= "Content-Type: {$type}\r\n";
			$body_part .= "\r\n" . file_get_contents($value);
			$body[] = $body_part;
		}
		$multipart_body = "--$boundary\r\n";
		$multipart_body .= implode("\r\n--$boundary\r\n", $body);
		$multipart_body .= "\r\n--$boundary--";
		return $multipart_body;
	}
}