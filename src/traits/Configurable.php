<?php

declare(strict_types=1);

namespace Verdient\http\traits;

/**
 * 可配置的
 * @author Verdient。
 */
trait Configurable
{
    /**
     * @param array $options 选项
     * @author Verdient。
     */
    public function __construct($options = [])
    {
        $this->configure($options);
    }

    /**
     * 配置
     * @param array $options 选项
     * @author Verdient。
     */
    public function configure($options)
    {
        foreach ($options as $name => $value) {
            if (property_exists($this, $name)) {
                if (!$value && $this->$name) {
                    continue;
                }
                $this->$name = $value;
            }
        }
    }
}
