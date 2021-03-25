<?php

declare(strict_types=1);

namespace Verdient\http\api;

/**
 * 结果
 * @author Verdient。
 */
class Result
{
    /**
     * @var int 响应是否成功
     * @author Verdient。
     */
    public $isOK = false;

    /**
     * @var array|string 响应的数据
     * @author Verdient。
     */
    public $data = null;

    /**
     * @var int 错误码
     * @author Verdient。
     */
    public $errorCode = null;

    /**
     * @var string 错误信息
     * @author Verdient。
     */
    public $errorMessage = null;
}