# HTTP Client
HTTP 客户端

## 创建新的请求实例
```php
use http\Request;

/**
 * 构建器配置 (可选)
 * 与$bodySerializer协同使用
 * 内置了三种序列化器，分别是：json，urlencoded，urlencoded
 * 可自行新增或覆盖相应的构建器
 */
$builders = [
	'json' => 'http\builder\JsonBuilder',
	'urlencoded' => 'http\builder\UrlencodedBuilder',
	'urlencoded' => 'http\builder\XmlBuilder'
];

/**
 * 解析器配置 (可选)
 * 内置了三种序列化器，分别是：
 *   application/json，
 *   application/x-www-form-urlencoded，
 *   application/xml
 * 可自行新增或覆盖相应的解析器
 */
$parsers = [
	'application/json' => 'http\parser\JsonParser',
	'application/x-www-form-urlencoded' => 'http\parser\UrlencodedParser',
	'application/xml' => 'http\parser\XmlParser',
];

/**
 * 消息体序列化器 (可选)
 * 可以是字符串也可以是一个回调函数
 * 当值为字符串时，会从$builders中选取与之对应的构建器进行序列化
 * 当值为回调函数时，会将消息体传入回调函数进行序列化，以结果作为序列化后的结果
 * 默认为json
 */
$bodySerializer = 'json';

/**
 * 当解析失败时是否尝试解析 (可选)
 * 默认组件会根据响应的Content-Type头部调用相应的解析器进行解析
 * 如果值为true，则会在解析失败的时候调用所有可用的解析器进行尝试解析
 * 来应对Content-Type返回错误或未返回的情况
 * 默认为true
 */
$tryParse = true;

$request = new Request([
	'builders' => $builders,
	'parsers' => $parsers,
	'bodySerializer' => $bodySerializer,
	'tryParse' => $tryParse
]);
```
## 设置请求参数
```php
$request->setUrl(${url}); //设置请求URL
$request->setMethod(${method}); //设置请求方法
$request->setHeader([${name} => ${value}, ...]); //设置请求头部
$request->setQuery([${name} => ${value}, ...]); //设置查询参数
$request->setBody([${name} => ${value}, ...]); //设置消息体参数
$request->setProxy(${address}, ${port}=null); //设置代理
$request->setOption(${name}, ${value}); //设置CURL参数
$request->setOptions([${name} => ${value}, ...]); //批量设置CURL参数
```
## 添加参数
`Header`, `Query`, `Body`均支持添加参数，相应方法为：
- addHeader(${name}, ${value})
- addFilterHeader(${name}, ${value})
- addQuery(${name}, ${value})
- addFilterQuery(${name}, ${value})
- addBody(${name}, ${value})
- addFilterBody(${name}, ${value})

其中`addFilterXXX`与`addXXX`的区别是`addFilterXXX`仅添加非空参数，而`addXXX`则无此限制
## 发送请求
```php
$request->send(${raw} = false);
```
`$raw`参数标识是否返回原文，默认为`false`。当值为`true`时，返回响应的原文，当值为`false`时，返回`Response`类的实例

## 指定方法直接请求
```php
$request->get(${raw} = false);
$request->head(${raw} = false);
$request->post(${raw} = false);
$request->put(${raw} = false);
$request->patch(${raw} = false);
$request->delete(${raw} = false);
$request->options(${raw} = false);
$request->trace(${raw} = false);
$request->request({$method}, ${raw} = false);
```

`$raw`含义与`send`方法相同

## 直接设置消息体
若消息体格式并非Key-Value格式或其他需要直接设置消息体的情况，可以直接调用
```php
$request->setContent(${data}, ${serializer} = null);
```
其中`$data`可以为`String`，`Array`或`Builder`及其子类的实例，`${serializer}`为字符串或匿名函数。`setContent`的优先级比`setBody`的优先级高，即设置了Content后无论是否设置Body，在发送时均会忽略Body的内容

## 获取响应原文
```php
$request->getResponse();
```

## 使用响应类
默认情况下，请求后会返回Response类的实例。Response类会对响应进行一些基本的处理，方便用于后续的操作
```php
$response = $request->send();
$response->getRawResponse(); //获取响应原文
$response->getRawContent(); //获取消息体原文
$response->getRawHeader(); //获取头部原文
$response->getBody(); //获取解析后的消息体参数
$response->getHeader(); //获取解析后的头部
$response->getCookie(); //获取解析后的Cookie
$response->getStatusCode(); //获取状态码
$response->getContentType(); //获取消息体类型
$response->getCharset(); //获取字符集
$response->getError(); //获取错误信息
$response->getErrorMessage(); //获取错误提示
$response->getErrorCode(); //获取错误码
$response->hasError(); //是否有错误
```
`Response`中`request`指向原来的请求对象，若需要使用`Request`中的内容，请使用`$response->request`