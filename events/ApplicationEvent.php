<?php

namespace app\common\events;

use app\common\libs\Application;

class ApplicationEvent
{

    protected $_app;

    protected $_profiler;

    /**
     * @var \Phalcon\Logger\Adapter\Stream $_logger
     */
    protected $_logger;

    public function __construct()
    {
        $this->_app = Application::getApp();
        $di = $this->_app->getDI();
        $this->_logger = $di->getShared('logger');
        //把事件放到load 事件中，或者application的构造事件中
//        Profiler::getInstance()->start('RequestProfile');
//        $time = $this->_app->getRequestTime();
//        $this->_logger->notice('Request start:' . date('Y-m-d H:i:s', $time));
//        $request = $this->_app->request;
//        $data = [
//            'request_method' => $request->getMethod(),
//            'get_param' => $_GET,
//            'post_param' => $_POST,
//            'headers' => $request->getHeaders(),
//            'client_addrs' => $request->getClientAddress()
//        ];
//        $this->_logger->info(json_encode($data));
    }

    public function boot($event)
    {
        $this->_logger->debug('start boot');
    }

    public function beforeStartModule($event)
    {
        $this->_logger->debug('before start module');
    }

    /**
     * @param $event
     * @param $application Application
     */
    public function beforeSendResponse($event,$application)
    {
        $this->_logger->debug('before response:');
    }

    public function beforeHandleRequest($event)
    {
        $this->_logger->debug('before handle request');
    }

    public function afterStartModule($event)
    {
        $this->_logger->debug('after start module');
    }

    public function afterHandleRequest($event)
    {
        $this->_logger->debug('after handle request');
    }

    public function viewRender($event)
    {
        $this->_logger->debug('view render');
    }
}