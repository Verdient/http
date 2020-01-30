<?php
namespace http\builder;

/**
 * FormDataBuilder
 * 表单构建器
 * ---------------
 * @author Verdient。
 */
class FormDataBuilder extends Builder
{
	/**
	 * @var String $contentType
	 * 消息体类型
	 * ------------------------
	 * @inheritdoc
	 * -----------
	 * @author Verdient。
	 */
	public $contentType = 'multipart/form-data';

	/**
	 * @var const TEXT
	 * 文本类型
	 * ---------------
	 * @author Verdient。
	 */
	const TEXT = 1;

	/**
	 * @var const FILE
	 * 文件类型
	 * ---------------
	 * @author Verdient。
	 */
	const FILE = 2;

	/**
	 * @var $_boundary
	 * 分隔符
	 * ---------------
	 * @author Verdient。
	 */
	protected $_boundary;

	/**
	 * getBoundary()
	 * 获取分隔符
	 * -------------
	 * @return String
	 * @author Verdient。
	 */
	public function getBoundary(){
		if(!$this->_boundary){
			$this->_boundary = hash('sha256', random_bytes(64));
		}
		return $this->_boundary;
	}


	/**
	 * addTexts(Array $data)
	 * 批量添加文本
	 * ---------------------
	 * @param Array $data 待添加的数据
	 * -----------------------------
	 * @return FormData
	 * @author Verdient。
	 */
	public function addTexts(Array $data){
		foreach($data as $name => $value){
			$this->addText($name, $value);
		}
		return $this;
	}

	/**
	 * addFiles(Array $data)
	 * 批量添加文件
	 * ---------------------
	 * @param Array $data 待添加的数据
	 * -----------------------------
	 * @return FormData
	 * @author Verdient。
	 */
	public function addFiles(Array $data){
		foreach($data as $name => $path){
			$this->addFile($name, $path);
		}
		return $this;
	}

	/**
	 * addText(String $name, String $value)
	 * 添加文本
	 * ------------------------------------
	 * @param String $name 名称
	 * @param String $value 内容
	 * ------------------------
	 * @return FormData
	 * @author Verdient。
	 */
	public function addText($name, $value){
		return $this->addElement($name, $value, static::TEXT);
	}

	/**
	 * addFile(String $name, String $path)
	 * 添加文件
	 * -----------------------------------
	 * @param String $name 名称
	 * @param String $value 内容
	 * ------------------------
	 * @return FormData
	 * @author Verdient。
	 */
	public function addFile($name, $path){
		return $this->addElement($name, $path, static::FILE);
	}

	/**
	 * addElement(String $name, String $value, Integer $type)
	 * 添加元素
	 * ------------------------------------------------------
	 * @param String $name 名称
	 * @param String $value 内容
	 * @param Integer $type 类型
	 * ------------------------
	 * @return FormData
	 * @author Verdient。
	 */
	public function addElement($name, $value, $type){
		$this->_elements[$name] = [$type, $value];
		return $this;
	}

	/**
	 * toString()
	 * 转换为字符串
	 * ----------
	 * @return String
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
		function convert_array_key(&$node, $prefix, &$result) {
			if(!is_array($node)){
				$result[$prefix] = $node;
			}else{
				foreach($node as $key => $value){
					convert_array_key($value, "{$prefix}[{$key}]", $result);
				}
			}
		}

		function query_multidimensional_array(&$array, $query) {
			$query = explode('][', substr($query, 1, -1));
			$temp = $array;
			foreach ($query as $key) {
				$temp = $temp[$key];
			}
			return $temp;
		}

		$body = [];

		foreach($texts as $key => $value){
			if(!is_array($value)){
				$body_part = "Content-Disposition: form-data; name=\"$key\"\r\n";
				$body_part .= "\r\n$value";
				$body[] = $body_part;
			}else{
				$result = [];
				convert_array_key($value, $key, $result);
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
			// $type = FileHelper::getMimeType($value);//todo
			$body_part = "Content-Disposition: form-data; name=\"$key\"; filename=\"{$value}\"\r\n";
			// $body_part .= "Content-type: {$type}\r\n";//todo
			$body_part .= "\r\n" . file_get_contents($value);
			$body[] = $body_part;
		}
		$multipart_body = "--$boundary\r\n";
		$multipart_body .= implode("\r\n--$boundary\r\n", $body);
		$multipart_body .= "\r\n--$boundary--";
		return $multipart_body;
	}
}