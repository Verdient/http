# HTTP Client

HTTP 客户端

## 创建新的请求实例

```php
use Verdient\http\Request;

$request = new Request();
```

## 设置请求参数

```php
$request->setUrl($url); //设置请求URL
$request->setMethod($method); //设置请求方法
$request->setHeaders([$name => $value, ...]); //设置请求头部
$request->setQuery([$name => $value, ...]); //设置查询参数
$request->setBody([$name => $value, ...]); //设置消息体参数
$request->setProxy($address, $port=null); //设置代理
$request->setTimeout($timeout); //设置超时时间
```

## 添加参数

`Header`, `Query`, `Body`均支持添加参数，相应方法为：

- addHeader($name, $value)
- addQuery($name, $value)
- addBody($name, $value)

## 设置消息体序列化器

```php
use Verdient\http\serializer\body\JsonBodySerializer;
$request->setBodySerializer(JsonBodySerializer::class);
```

可通过实现`Verdient\http\serializer\body\BodySerializerInterface`来实现任意的消息体序列化

## 设置查询参数序列化器

```php
use Verdient\http\serializer\query\RFC1738Serializer;
$request->setQuerySerializer(RFC1738Serializer::class);
```

可通过实现`Verdient\http\serializer\SerializerInterface`来实现任意的查询参数序列化

## 直接设置消息体

若消息体格式并非 Key-Value 格式或其他需要直接设置消息体的情况，可以直接调用

```php
$request->setContent($data);
```

`setContent`与`setBody`为互斥关系，调用的`setContent`会清除`setBody`设置的内容，反之亦然

## 发送请求

```php
$response = $request->send();
```

## 响应

```php
$response->getRawResponse(); //获取响应原文
$response->getRawContent(); //获取消息体原文
$response->getRawHeaders(); //获取头部原文
$response->getBody(); //获取解析后的消息体参数
$response->getHeaders(); //获取解析后的头部
$response->getCookies(); //获取解析后的Cookie
$response->getStatusCode(); //获取状态码
$response->getContentType(); //获取消息体类型
$response->getCharset(); //获取字符集
$response->getStatusMessage(); //获取状态消息
$response->getHttpVersion(); //获取HTTP版本
$response->getRequest(); //获取请求对象
```

## 批量请求

```php
use Verdient\http\BatchRequest;

/**
 * 请求对象的集合
 * 集合内的元素必须是Request的实例
 */
$requests = [];

for($i = 0; $i < 100; $i++){
    $request = new Request();
    $request->setUrl($url);
    $request->addQuery('id', $i);
    $requests[] = $request;
}

/**
 * 批大小，默认为100
 */
$batchSize = 100;

$batch = new BatchRequest($requests, $batchSize);

/**
 * 返回内容为数组，keyValue对应关系与构造BatchRequest时传入的数组相同
 * 遍历返回的结果，结果与Request调用send方法后返回的内容一致，使用方法也相同
 */
$response = $batch->send();
```
