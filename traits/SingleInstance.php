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
        return self::$_instance;
    }
}