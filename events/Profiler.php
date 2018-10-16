<?php

namespace app\core\events;

class Profiler
{
    protected static  $_instance;

    protected function __construct()
    {
    }

    public static function getInstance()
    {
        if (!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    protected $_container;

    public function start($name)
    {
        $data = [
            'start_time' => microtime(true),
            'start_mem' => memory_get_usage()
        ];
        $this->_container[$name] = $data;
    }

    public function end($name)
    {
        if (!isset($this->_container[$name])) {
            return null;
        }
        $data = $this->_container[$name];
        $data['end_time'] = microtime(true);
        $data['end_mem'] = memory_get_usage();
        $this->_container[$name] = $data;
        //同时计算 消耗的时间 和内存
        $useTime = $data['end_time'] - $data['start_time'];
        $useMem = $data['end_mem'] - $data['start_mem'];
        return [
            'use_time' => $useTime,
            'use_mem' => $useMem . ' bytes'
        ];
    }
}