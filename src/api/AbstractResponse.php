<?php

declare(strict_types=1);

namespace Verdient\http\api;

use chorus\UnknownPropertyException;
use chorus\UnsatisfiedExcepiton;
use Verdient\http\component\Response;

/**
 * 抽象响应
 * @author Verdient。
 */
abstract class AbstractResponse
{
    /**
     * @var bool 请求是否成功
     * @author Verdient。
     */
    protected $isOK = false;

    /**
     * @var int 错误码
     * @author Verdient。
     */
    protected $errorCode = null;

    /**
     * @var string 错误信息
     * @author Verdient。
     */
    protected $errorMessage = null;

    /**
     * @var Response 响应对象
     * @author Verdient。
     */
    protected $response = null;

    /**
     * @var array 属性配置
     * @author Verdient。
     */
    protected $attributes = [];

    /**
     * @var array 数据
     * @author Verdient。
     */
    protected $data = null;

    /**
     * @var Response 响应对象
     * @author Verdient。
     */
    public function __construct(Response $response){
        $this->response = $response;
        $this->attributes = $this->attributes();
        $result = $this->normailze($response);
        $this->isOK = $result->isOK;
        if($this->isOK){
            $data = $result->data;
            if(!empty($this->attributes)){
                foreach($this->attributes as $name => $type){
                    $this->data[$name] = isset($data[$name]) ? Formatter::format($type, $data[$name]) : null;
                }
            }else{
                $this->data = $data;
            }
        }else{
            $this->errorCode = $result->errorCode ?: $response->getStatusCode();
            $this->errorMessage = $result->errorMessage ?: $response->getRawContent();
        }
    }

    /**
     * 格式化
     * @param Response 响应
     * @return Result
     * @author Verdient。
     */
    abstract public function normailze(Response $response): Result;

    /**
     * 获取是否成功
     * @return bool
     * @author Verdient。
     */
    public function getIsOK(){
        return $this->isOK;
    }

    /**
     * 获取错误码
     * @return int
     * @author Verdient。
     */
    public function getErrorCode(){
        return $this->errorCode;
    }

    /**
     * 获取错误信息
     * @return string
     * @author Verdient。
     */
    public function getErrorMessage(){
        return $this->errorMessage;
    }

    /**
     * 获取返回的数据
     * @return array
     * @author Verdient。
     */
    public function getData(){
        return $this->data;
    }

    /**
     * @inheritdoc
     * @throws Exception
     * @return mixed
     * @author Verdient。
     */
    public function __get($name){
        if(!array_key_exists($name, $this->attributes)){
            throw new UnknownPropertyException('Getting unknown property ' . static::class . '::' . $name);
        }
        if(!$this->getIsOK()){
            throw new UnsatisfiedExcepiton('The request was unsuccessful, please check and try again');
        }
        return $this->data[$name];
    }

    /**
     * 属性配置
     * @return array
     * @author Verdinet。
     */
    abstract protected function attributes(): array;
}