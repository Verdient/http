# HTTP Client

HTTP 客户端

## 创建新的请求实例

```php
use Verdient\Http\Request;

$request = new Request();
```

## 设置请求参数

```php
$request->setUrl(string $url); //设置请求URL
$request->setMethod(string $method); //设置请求方法
$request->setHeaders([$name => $value, ...]); //设置请求头部
$request->setQueries([$name => $value, ...]); //设置查询参数
$request->setBodies([$name => $value, ...]); //设置消息体参数
$request->setProxy(string $address, ?int $port = null); //设置代理
$request->setTimeout(int $timeout); //设置超时时间
```

## 添加参数

`Header`, `Query`, `Body`均支持添加参数，相应方法为：

```php
- addHeader(string $key, string|array $value)
- addQuery(string $name, string|array $value)
- addBody(string $name, mixed $value)
```

## 设置消息体序列化器

```php
use Verdient\Http\Serializer\Body\JsonBodySerializer;
$request->setBodySerializer(new JsonBodySerializer);
```

可通过实现`Verdient\Http\Serializer\Body\BodySerializerInterface`来实现任意的消息体序列化

## 设置查询参数序列化器

```php
use Verdient\Http\Serializer\Query\RFC1738Serializer;
$request->setQuerySerializer(new RFC1738Serializer);
```

可通过实现`Verdient\Http\Serializer\SerializerInterface`来实现任意的查询参数序列化

## 直接设置消息体

若消息体格式并非 Key-Value 格式或其他需要直接设置消息体的情况，可以直接调用

```php
$request->setContent(string $content);
```

`setContent`与`setBody`为互斥关系，调用的`setContent`会清除`setBody`设置的内容，反之亦然

## 发送请求

```php
$result = $request->send();
```

## 响应结果

```php

# 请求返回结果对象，可获取部分常用的数据

$result->getIsOK(): bool; //获取请求是否成功
$result->getRequest(): Request; //获取请求对象
$result->getErrorCode(): ?int // 获取错误码
$result->getErrorMessage(): ?string // 获取错误信息
$result->getStatusCode(): ?int // 获取HTTP状态码
$result->getHeaders(): ?array // 获取头部响应数据
$result->getBodies(): ?array // 获取HTTP消息体响应数据

# 可以通过getResponse获取响应对象，来实现更多的操作
$response = $result->getResponse(): Response; //获取响应对象

$response->getRawResponse(): string; //获取响应原文
$response->getRawStatus(): string; //获取状态行原文
$response->getRawHeaders(): string; //获取头部原文
$response->getRawContent(): ?string; //获取消息体原文
$response->getStatusCode(): int; //获取状态码
$response->getHeaders(): array; //获取解析后的头部
$response->getBodies(): mixed; //获取解析后的消息体参数
$response->getCookies(): array; //获取解析后的Cookie
$response->getContentType(): ?string; //获取消息体类型
$response->getCharset(): ?string; //获取字符集
$response->getStatusMessage(): string; //获取状态消息
$response->getHttpVersion(): string; //获取HTTP版本
$response->getRequest(): Request; //获取请求对象
```

## 批量请求

```php
use Verdient\Http\BatchRequest;

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
$result = $batch->send();
```
