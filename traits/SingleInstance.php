<?php

namespace app\core\traits;

trait SingleInstance
{
    /**
     * @var static
     */
    protected static $_instance;

    public static function getInstance($reinit = false)
    {
        if (static::$_instance && !$reinit) {
            return static::$_instance;
        }
        static::$_instance = new static();
        static::$_instance->init(); //在构造函数中初始化出问题 有时不好找问题
        return static::$_instance;
    }

    public function init()
    {

    }
}