<?php

namespace app\common\events;

use app\common\libs\Application;
use Phalcon\Events\Event;
use Phalcon\Loader;

class LoadEvent
{
    protected $_app;

    public function __construct()
    {
        $this->_app  = Application::getApp();
        $this->_logger = $this->_app->getDI()->getShared('logger');
    }

    /**
     * @param $event Event
     * @param $loader Loader
     */
    public function beforeCheckPath($event, $loader)
    {
        $this->_logger->notice("before load file:" . $loader->getCheckedPath());
    }

    /**
     * @param $event Event
     * @param $loader Loader
     */
    public function pathFound($event, $loader)
    {
        $file = $loader->getFoundPath();
        $this->_app->setLoadFile($file);
        $this->_logger->debug('load file:' . $file);
    }
}