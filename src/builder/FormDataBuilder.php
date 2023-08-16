<?php

namespace Verdient\http\builder;

use Verdient\http\serializer\body\FormDataSerializer;

/**
 * 表单序列化器
 * @author Verdient。
 */
class FormDataBuilder implements BuilderInterface
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function serializer(): string
    {
        return FormDataSerializer::class;
    }

    /**
     * @var string 分隔符
     * @author Verdient。
     */
    protected $boundary;

    /**
     * @var string[] 文本参数
     * @author Verdient。
     */
    protected $texts = [];

    /**
     * @var string[] 文件参数
     * @author Verdient。
     */
    protected $files = [];

    /**
     * 获取文本参数
     * @return array
     * @author Verdient。
     */
    public function getTexts()
    {
        return $this->texts;
    }

    /**
     * 获取文件参数
     * @return array
     * @author Verdient。
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * 添加文本
     * @param string $name 名称
     * @param string $value 内容
     * @return static
     * @author Verdient。
     */
    public function addText($name, $value)
    {
        $this->texts[$name] = $value;
        return $this;
    }

    /**
     * 移除文本
     * @param string $name 名称
     * @return static
     * @author Verdient。
     */
    public function removeText($name)
    {
        unset($this->texts[$name]);
        return $this;
    }

    /**
     * 添加文件
     * @param string $name 名称
     * @param string $value 内容
     * @return static
     * @author Verdient。
     */
    public function addFile($name, $path)
    {
        $this->files[$name] = $path;
        return $this;
    }

    /**
     * 移除文件
     * @param string $name 名称
     * @return static
     * @author Verdient。
     */
    public function removeFile($name, $path)
    {
        unset($this->files[$name]);
        return $this;
    }

    /**
     * 获取分隔符
     * @return string
     * @author Verdient。
     */
    public function getBoundary()
    {
        if (!$this->boundary) {
            $this->boundary = hash('sha256', random_bytes(64));
        }
        return $this->boundary;
    }
}
