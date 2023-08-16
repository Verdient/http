<?php

namespace Verdient\http\serializer\body;

use Exception;
use Verdient\http\serializer\FormDataBuilder;

/**
 * 表单数据序列化器
 * @author Verdient。
 */
class FormDataSerializer implements BodySerializerInterface
{
    /**
     * @inheritdoc
     * @param FormDataBuilder $data 待序列化的数据
     * @author Verdient。
     */
    public static function serialize($data): string
    {
        $boundary = $data->getBoundary();
        $body = [];
        foreach ($data->getTexts() as $key => $value) {
            if (!is_array($value)) {
                $body_part = "Content-Disposition: form-data; name=\"$key\"\r\n";
                $body_part .= "\r\n$value";
                $body[] = $body_part;
            } else {
                $result = [];
                static::convertArrayKey($value, $key, $result);
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
     * @param mixed 当前节点
     * @param string $prefix 前缀
     * @param array $result 结果
     * @author Verdient。
     */
    protected static function convertArrayKey(&$node, $prefix, &$result)
    {
        if (!is_array($node)) {
            $result[$prefix] = $node;
        } else {
            foreach ($node as $key => $value) {
                static::convertArrayKey($value, "{$prefix}[{$key}]", $result);
            }
        }
    }

    /**
     * @inheritdoc
     * @param FormDataBuilder $data 待序列化的数据
     * @author Verdient。
     */
    public static function headers($data): array
    {
        return [
            'Content-Type' => 'multipart/form-data; boundary=' . $data->getBoundary()
        ];
    }
}
