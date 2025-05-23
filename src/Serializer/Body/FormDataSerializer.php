<?php

namespace Verdient\Http\Serializer\Body;

use Exception;
use Verdient\Http\Builder\FormDataBuilder;

/**
 * 表单数据序列化器
 *
 * @author Verdient。
 */
class FormDataSerializer implements BodySerializerInterface
{
    /**
     * @inheritdoc
     * @param FormDataBuilder $data 待序列化的数据
     * @author Verdient。
     */
    public function serialize(mixed $data): string
    {
        if (empty($data->getTexts()) && empty($data->getFiles())) {
            return '';
        }

        $boundary = $data->getBoundary();

        $body = [];

        foreach ($data->getTexts() as $key => $value) {
            if (!is_array($value)) {
                $body_part = "Content-Disposition: form-data; name=\"$key\"\r\n";
                $body_part .= "\r\n$value";
                $body[] = $body_part;
            } else {
                $result = [];
                $this->convertArrayKey($value, $key, $result);
                foreach ($result as $k => $v) {
                    $body_part = "Content-Disposition: form-data; name=\"$k\"\r\n";
                    $body_part .= "\r\n$v";
                    $body[] = $body_part;
                }
            }
        }

        foreach ($data->getFiles() as $key => $value) {
            if (!file_exists($value)) {
                throw new Exception('file ' . $value . ' does not exist');
            }
            $type = mime_content_type($value);
            $filename = pathinfo($value, PATHINFO_BASENAME);
            $body_part = "Content-Disposition: form-data; name=\"$key\"; filename=\"{$filename}\"\r\n";
            $body_part .= "Content-Type: {$type}\r\n";
            $body_part .= "\r\n" . file_get_contents($value);
            $body[] = $body_part;
        }

        $multipart_body = "--$boundary\r\n";
        $multipart_body .= implode("\r\n--$boundary\r\n", $body);
        $multipart_body .= "\r\n--$boundary--";

        return $multipart_body;
    }

    /**
     * 转换数组键值
     *
     * @param mixed 当前节点
     * @param string $prefix 前缀
     * @param array $result 结果
     * @author Verdient。
     */
    protected function convertArrayKey(&$node, $prefix, &$result)
    {
        if (!is_array($node)) {
            $result[$prefix] = $node;
        } else {
            foreach ($node as $key => $value) {
                $this->convertArrayKey($value, "{$prefix}[{$key}]", $result);
            }
        }
    }

    /**
     * @inheritdoc
     * @param FormDataBuilder $data 待序列化的数据
     * @author Verdient。
     */
    public function headers(mixed $data): array
    {
        if (!($data instanceof FormDataBuilder)) {
            return [];
        }

        if (empty($data->getTexts()) && empty($data->getFiles())) {
            return [];
        }

        return [
            'Content-Type' => 'multipart/form-data; boundary=' . $data->getBoundary()
        ];
    }
}
