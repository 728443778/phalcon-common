<?php

namespace app\common\traits;

trait SingleInstance
{
    /**
     * @var static
     */
    protected static $_instance;

    public static function getInstance()
    {
        if (self::$_instance) {
            return self::$_instance;
        }
        self::$_instance = new self();
        self::$_instance->init(); //在构造函数中初始化出问题 有时不好找问题
        return self::$_instance;
    }

    public function init()
    {

    }
}