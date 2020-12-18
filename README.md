# HTTP Client
HTTP 客户端

## 创建新的请求实例
```php
use Verdient\http\Request;

/**
 * 构建器配置 (可选)
 * 与$bodySerializer协同使用
 * 内置了三种序列化器，分别是：json，urlencoded，xml
 * 可自行新增或覆盖相应的构建器
 */
$builders = [
	'json' => 'Verdient\http\builder\JsonBuilder',
	'urlencoded' => 'Verdient\http\builder\UrlencodedBuilder',
	'xml' => 'Verdient\http\builder\XmlBuilder'
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
	'application/json' => 'Verdient\http\parser\JsonParser',
	'application/x-www-form-urlencoded' => 'Verdient\http\parser\UrlencodedParser',
	'application/xml' => 'Verdient\http\parser\XmlParser',
];

/**
 * 传输组件配置
 * 内置了三种传输组件，分别是：
 *   cUrl, 基于cUrl的传输组件
 *   coroutine 基于Swoole的协程的传输组件
 *   stream 基于Streams的传输组件
 * 可自行新增或覆盖相应的传输组件
 */
$transports = [
	'cUrl' => 'Verdient\http\transport\CUrlTransport',
	'coroutine' => 'Verdient\http\transport\CoroutineTransport',
	'stream' => 'Verdient\http\transport\StreamTransport'
];

/**
 * 传输组件，默认为cUrl
 */
$transport = 'cUrl';

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
	'transports' => $transports,
	'transport' => $transport,
	'bodySerializer' => $bodySerializer,
	'tryParse' => $tryParse
]);
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
- addFilterHeader($name, $value)
- addQuery($name, $value)
- addFilterQuery($name, $value)
- addBody($name, $value)
- addFilterBody($name, $value)

其中`addFilterXXX`与`addXXX`的区别是`addFilterXXX`仅添加非空参数，而`addXXX`则无此限制
## 直接设置消息体
若消息体格式并非Key-Value格式或其他需要直接设置消息体的情况，可以直接调用
```php
$request->setContent($data, $serializer = null);
```
其中`$data`可以为`String`，`Array`或`Builder`及其子类的实例，`$serializer`为字符串或匿名函数。`setContent`的优先级比`setBody`的优先级高，即设置了Content后无论是否设置Body，在发送时均会忽略Body的内容
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
```
`Response`中`request`指向原来的请求对象，若需要使用`Request`中的内容，请使用`$response->request`
## 事件
Request 内置请求前事件（Request::EVENT_BEFORE_REQUEST）和请求后（Request::EVENT_AFTER_REQUEST）事件，可使用on函数挂载事件

事件触发时会将当前Request对象以参数的形式传递给相应处理函数
```php
$request->on(Request::EVENT_BEFORE_REQUEST, function($request){

});
$request->on(Request::EVENT_AFTER_REQUEST, function($request, $response){

});
```
## 批量请求
```php
use Verdient\http\BatchRequest;

/**
 * 传输组件配置
 * 内置了三种传输组件，分别是：
 *   cUrl, 基于cUrl的传输组件
 *   coroutine 基于Swoole的协程的传输组件
 *   stream 基于Streams的传输组件
 * 可自行新增或覆盖相应的传输组件
 */
$transports = [
	'cUrl' => 'Verdient\http\transport\CUrlTransport',
	'coroutine' => 'Verdient\http\transport\CoroutineTransport',
	'stream' => 'Verdient\http\transport\StreamTransport'
];

/**
 * 传输组件，默认为cUrl
 */
$transport = 'cUrl';

/**
 * 批大小，默认为100
 */
$batchSize = 100;

$batch = new BatchRequest([
	'batchSize' => $batchSize,
	'transports' => $transports,
	'transport' => $transport,
]);

/**
 * 请求对象的集合
 * 集合内的元素必须是Verdient\http\Request的实例
 */
$requests = [];

for($i = 0; $i < 100; $i++){
	$request = new Request();
	$request->setUrl($url);
	$request->addQuery('id', $i);
	$requests[] = $request;
}

$batch->setRequests($requests);

/**
 * 返回内容为数组，keyValue对应关系与构造BatchRequest时传入的数组相同
 * 遍历返回的结果，结果与Request调用send方法后返回的内容一致，使用方法也相同
 */
$response = $batch->send();
```