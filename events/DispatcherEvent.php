<?php

namespace app\core\events;

use app\core\libs\Application;
use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;

class DispatcherEvent
{
    protected $catchException = false;

    protected $_app;

    public function __construct()
    {
        $this->_app  = Application::getApp();
        $this->_logger = $this->_app->getDI()->getShared('logger');
    }

    /**
     * @param $event Event
     * @param $dispatcher Dispatcher
     */
    public function afterExecuteRoute($event, $dispatcher)
    {
        if ($this->_app->debug) {
            $data = [
                'module' => $dispatcher->getModuleName(),
                'controller' => $dispatcher->getControllerName(),
                'action' => $dispatcher->getActionName()
            ];
            $this->_logger->debug('after route:' . json_encode($data));
        }
    }

    public function afterDispatch($event, $dispatcher)
    {
        if ($this->_app->debug) {
            $data = [
                'module' => $dispatcher->getModuleName(),
                'controller' => $dispatcher->getControllerName(),
                'action' => $dispatcher->getActionName()
            ];
            $this->_logger->debug('after dispatch:' . json_encode($data));
        }
    }

    public function afterDispatchLoop($event, $dispatcher)
    {
        if ($this->_app->debug) {
            $data = [
                'module' => $dispatcher->getModuleName(),
                'controller' => $dispatcher->getControllerName(),
                'action' => $dispatcher->getActionName()
            ];
            $this->_logger->debug('after dispatch loop:' . json_encode($data));
        }
    }

    public function afterInitialize($event, $dispatcher)
    {
        if ($this->_app->debug) {
            $data = [
                'module' => $dispatcher->getModuleName(),
                'controller' => $dispatcher->getControllerName(),
                'action' => $dispatcher->getActionName()
            ];
            $this->_logger->debug('after initialize:' . json_encode($data));
        }
    }

    /**
     * @param $event Event
     * @param $dispatcher Dispatcher
     */
    public function beforeException($event, $dispatcher)
    {
        if ($this->catchException) {
            return;
        }
        $exception = $event->getData();
        $message = [
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ];
        $this->_logger->debug('catch exception:' .  json_encode($message));
        $this->catchException = true;
    }

    public function beforeExecuteRoute($event, $dispatcher)
    {
        if ($this->_app->debug) {
            $data = [
                'module' => $dispatcher->getModuleName(),
                'controller' => $dispatcher->getControllerName(),
                'action' => $dispatcher->getActionName()
            ];
            $this->_logger->debug('before execute route:' . json_encode($data));
        }
    }

    public function beforeDispatch($event, $dispatcher)
    {
        if ($this->_app->debug) {
            $data = [
                'module' => $dispatcher->getModuleName(),
                'controller' => $dispatcher->getControllerName(),
                'action' => $dispatcher->getActionName()
            ];
            $this->_logger->debug('before dispatch:' . json_encode($data));
        }
    }

    /**
     * @param $event Event
     * @param $dispatcher Dispatcher
     */
    public function beforeDispatchLoop($event, $dispatcher)
    {
        if ($this->_app->debug) {
            $data = [
                'module' => $dispatcher->getModuleName(),
                'controller' => $dispatcher->getControllerName(),
                'action' => $dispatcher->getActionName()
            ];
            $this->_logger->debug('before dispatch loop:' . json_encode($data));
        }
    }

    public function beforeForward($event, $dispatcher)
    {
        if ($this->_app->debug) {
            $data = [
                'module' => $dispatcher->getModuleName(),
                'controller' => $dispatcher->getControllerName(),
                'action' => $dispatcher->getActionName()
            ];
            $this->_logger->debug('before forward:' . json_encode($data));
        }
    }

    public function beforeNotFoundAction($event, $dispatcher)
    {
        //do nothing
    }
}