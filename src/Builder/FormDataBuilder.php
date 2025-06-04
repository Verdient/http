<?php

namespace Verdient\Http\Builder;

use Verdient\Http\Serializer\Body\FormDataSerializer;
use Verdient\Http\Serializer\SerializerInterface;

/**
 * 表单构建器
 *
 * @author Verdient。
 */
class FormDataBuilder implements BuilderInterface
{
    /**
     * @inheritdoc
     * @author Verdient。
     */
    public function serializer(): SerializerInterface
    {
        return new FormDataSerializer;
    }

    /**
     * 分隔符
     *
     * @author Verdient。
     */
    protected ?string $boundary = null;

    /**
     * 文本参数
     *
     * @var string[]
     * @author Verdient。
     */
    protected array $texts = [];

    /**
     * 文件参数
     *
     * @var string[]
     * @author Verdient。
     */
    protected array $files = [];

    /**
     * 获取文本参数
     *
     * @return string[]
     * @author Verdient。
     */
    public function getTexts(): array
    {
        return $this->texts;
    }

    /**
     * 获取文件参数
     *
     * @return string[]
     * @author Verdient。
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * 添加文本
     *
     * @param string $name 名称
     * @param string $value 内容
     * @author Verdient。
     */
    public function addText(string $name, string $value): static
    {
        $this->texts[$name] = $value;
        return $this;
    }

    /**
     * 移除文本
     *
     * @param string $name 名称
     * @author Verdient。
     */
    public function removeText(string $name): static
    {
        unset($this->texts[$name]);
        return $this;
    }

    /**
     * 添加文件
     *
     * @param string $name 名称
     * @param string $value 内容
     * @author Verdient。
     */
    public function addFile(string $name, string $path): static
    {
        $this->files[$name] = $path;
        return $this;
    }

    /**
     * 移除文件
     *
     * @param string $name 名称
     * @author Verdient。
     */
    public function removeFile(string $name): static
    {
        unset($this->files[$name]);
        return $this;
    }

    /**
     * 获取分隔符
     *
     * @author Verdient。
     */
    public function getBoundary(): string
    {
        if ($this->boundary === null) {

            $this->boundary = '----WebKitFormBoundary';

            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

            $max = strlen($chars) - 1;

            for ($i = 0; $i < 16; $i++) {
                $this->boundary .= $chars[random_int(0, $max)];
            }
        }
        return $this->boundary;
    }
}
