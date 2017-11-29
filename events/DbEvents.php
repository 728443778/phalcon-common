<?php

namespace app\common\events;

use app\common\libs\Application;
use Phalcon\Db\Profiler;

class DbEvents
{
    protected $_profiler;

    protected $_logger;

    protected $_beforeTime;

    protected $_afterTime;

    protected $_isStart = false;

    public function __construct()
    {
        $this->_profiler = new Profiler();
//        $formaater = new \Phalcon\Logger\Formatter\Line('%message%');
        $application  = Application::getApp();
        $this->_logger = $application->logger;
//        $this->_logger->setFormatter($formaater);
    }

    public function beforeQuery($event, $connection)
    {
        if (!$this->_isStart) {
            $this->_isStart = true;
            $date = date('Y-m-d H:i:s');
            $this->_logger->info('DB Start:' . $date);
        }
        $this->_profiler->startProfile($connection->getSQLStatement());
    }

    public function afterQuery($event, $connection)
    {
        $this->_profiler->stopProfile();
        $userTime = $this->_profiler->getTotalElapsedSeconds();
        $message = 'sql:' . $connection->getSQLStatement() . "\n";
        $message .='DB耗时:' . $userTime;
        $this->_logger->info($message);
    }

    public function getProfiler()
    {
        return $this->_profiler;
    }
}