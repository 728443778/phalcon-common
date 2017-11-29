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
        $this->_logger = $application->getDI()->getShared('debug_logger');
//        $this->_logger->setFormatter($formaater);
    }

    public function beforeQuery($event, $connection)
    {
        $this->_logger->debug('execute sql:' . $connection->getSQLStatement());
        $this->_profiler->startProfile($connection->getSQLStatement());
    }

    public function afterQuery($event, $connection)
    {
        $this->_profiler->stopProfile();
        $userTime = $this->_profiler->getTotalElapsedSeconds();
        $message = 'sql:' . $connection->getSQLStatement() . "\n";
        $message .='DB耗时:' . $userTime;
        $this->_logger->debug($message);
    }

    public function getProfiler()
    {
        return $this->_profiler;
    }

    public function beginTransaction($event, $connection)
    {
        $this->_logger->debug('start transaction:');
    }

    public function commitTransaction($event, $connection)
    {
        $this->_logger->debug('commit transaction');
    }

    public function rollbackTransaction($event, $connection)
    {
        $this->_logger->debug('rollback transaction');
    }

    public function createSavepoint($event, $connection)
    {
        $this->_logger->debug('create save point');
    }

    public function releaseSavepoint($event, $connection)
    {
        $this->_logger->debug('release save point');
    }

    public function rollbackSavepoint($event, $connection)
    {
        $this->_logger->debug('rollback save point');
    }
}