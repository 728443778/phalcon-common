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

    protected $_app;

    public function __construct()
    {
        $this->_profiler = new Profiler();
        $this->_app = $application  = Application::getApp();
        $this->_logger = $application->getDI()->getShared('debug_logger');
    }

    public function beforeQuery($event, $connection)
    {
        ++$this->_app->_dbCount;
        $this->_logger->debug('execute sql:' . $connection->getSQLStatement());
        $this->_profiler->startProfile($connection->getSQLStatement());
    }

    public function afterQuery($event, $connection)
    {
        $this->_profiler->stopProfile();
        $userTime = $this->_profiler->getTotalElapsedSeconds();
        $this->_app->_dbOpTime += $userTime;
        $message = 'sql:' . $connection->getSQLStatement() . "\n";
        $message .='DB耗时:' . $userTime;
        $this->_logger->debug($message);
    }

    public function getProfiler()
    {
        return $this->_profiler;
    }

    /**
     * @param $event \Phalcon\Events\Event
     * @param $connection \Phalcon\Db\Adapter\Pdo\Mysql
     */
    public function beginTransaction($event, $connection)
    {
        $id = $connection->getConnectionId();
        $key = json_encode([
            'connection_id' => $id,
            'level' => $connection->getTransactionLevel()
        ]);
        \app\common\events\Profiler::getInstance()->start($key);
        $this->_logger->debug('start transaction:' . $key);
    }

    /**
     * @param $event \Phalcon\Events\Event
     * @param $connection \Phalcon\Db\Adapter\Pdo\Mysql
     */
    public function commitTransaction($event, $connection)
    {
        $id = $connection->getConnectionId();
        $key = json_encode([
            'connection_id' => $id,
            'level' => $connection->getTransactionLevel()
        ]);
        $result = \app\common\events\Profiler::getInstance()->end($key);
        $this->_logger->debug('commit transaction:');
        if ($result) {
            $this->_logger->debug($key . '=>' . json_encode($result));
        }
    }

    /**
     * @param $event \Phalcon\Events\Event
     * @param $connection \Phalcon\Db\Adapter\Pdo\Mysql
     */
    public function rollbackTransaction($event, $connection)
    {
        $id = $connection->getConnectionId();
        $key = json_encode([
            'connection_id' => $id,
            'level' => $connection->getTransactionLevel()
        ]);
        $result = \app\common\events\Profiler::getInstance()->end($key);
        $this->_logger->debug('rollback transaction');
        if ($result) {
            $this->_logger->debug($key . '=>' . json_encode($result));
        }
    }

    /**
     * @param $event \Phalcon\Events\Event
     * @param $connection \Phalcon\Db\Adapter\Pdo\Mysql
     */
    public function createSavepoint($event, $connection)
    {
        $this->_logger->debug('create save point');
    }

    /**
     * @param $event \Phalcon\Events\Event
     * @param $connection \Phalcon\Db\Adapter\Pdo\Mysql
     */
    public function releaseSavepoint($event, $connection)
    {
        $this->_logger->debug('release save point');
    }

    /**
     * @param $event \Phalcon\Events\Event
     * @param $connection \Phalcon\Db\Adapter\Pdo\Mysql
     */
    public function rollbackSavepoint($event, $connection)
    {
        $this->_logger->debug('rollback save point');
    }
}