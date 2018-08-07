<?php

namespace app\common\events;

use app\common\libs\Application;
use Phalcon\Db\Adapter\Pdo\Mysql;

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
        $this->_app = $application  = Application::getApp();
        $this->_logger = $application->getDI()->getShared('logger');
    }

    /**
     * @param $event
     * @param $connection Mysql
     */
    public function beforeQuery($event, $connection)
    {
        if ($this->_app->debug || $this->_app->profile) {
            ++$this->_app->_dbCount;
            $sql = $this->getSql($connection);
            $this->_logger->notice('sql:' . $sql);
            \app\common\events\Profiler::getInstance()->start($sql);
        }
    }

    public function afterQuery($event, $connection)
    {
        if ($this->_app->debug || $this->_app->profile) {
            $sql = $this->getSql($connection);
            $profierResult = Profiler::getInstance()->end($sql);
            Application::getApp()->_dbOpTime += $profierResult['use_time'];
            $message = 'sql:' . $sql . ';';
            $message .='DB耗时:' . $profierResult['use_time'];
            $this->_logger->notice($message);
        }
    }

    public function beforeDelete($event, $connection)
    {
        $sql = $this->getSql($connection);
        Profiler::getInstance()->start($sql);
    }

    public function afterDelete($event, $connection)
    {
        $sql = $this->getSql($connection);
        $profierResult = Profiler::getInstance()->end($sql);
        Application::getApp()->_dbOpTime += $profierResult['use_time'];
        $message = 'sql:' . $sql . ';';
        $message .='DB耗时:' . $profierResult['use_time'];
        $this->_logger->notice($message);
    }

    public function beforeInsert($event, $connection)
    {
        $sql = $this->getSql($connection);
        $this->_profiler->startProfile($sql);
    }

    /**
     * @param $connection Mysql
     */
    public function getSql($connection)
    {
        return $connection->getRealSQLStatement() . ';bind values:' . json_encode($connection->getSqlVariables());
    }

    public function afterInsert($event, $connection)
    {
        $sql = $this->getSql($connection);
        $profierResult = Profiler::getInstance()->end($sql);
        Application::getApp()->_dbOpTime += $profierResult['use_time'];
        $message = 'sql:' . $sql . ';';
        $message .='DB耗时:' . $profierResult['use_time'];
        $this->_logger->notice($message);
    }

    public function beforeUpdate($event, $connection)
    {
        $sql = $this->getSql($connection);
        $this->_profiler->startProfile($sql);
    }

    public function afterUpdate($event, $connection)
    {
        $sql = $this->getSql($connection);
        $profierResult = Profiler::getInstance()->end($sql);
        Application::getApp()->_dbOpTime += $profierResult['use_time'];
        $message = 'sql:' . $sql . ';';
        $message .='DB耗时:' . $profierResult['use_time'];
        $this->_logger->notice($message);
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
        if ($this->_app->debug || $this->_app->profile) {
            $id = $connection->getConnectionId();
            $key = json_encode([
                'connection_id' => $id,
                'level' => $connection->getTransactionLevel()
            ]);
            \app\common\events\Profiler::getInstance()->start($key);
            $this->_logger->notice('start transaction:' . $key);
        }
    }

    /**
     * @param $event \Phalcon\Events\Event
     * @param $connection \Phalcon\Db\Adapter\Pdo\Mysql
     */
    public function commitTransaction($event, $connection)
    {
        if ($this->_app->debug || $this->_app->profile) {
            $id = $connection->getConnectionId();
            $key = json_encode([
                'connection_id' => $id,
                'level' => $connection->getTransactionLevel()
            ]);
            $result = \app\common\events\Profiler::getInstance()->end($key);
            $this->_logger->notice('commit transaction:');
            if ($result) {
                $this->_logger->notice($key . '=>' . json_encode($result));
            }
        }
    }

    /**
     * @param $event \Phalcon\Events\Event
     * @param $connection \Phalcon\Db\Adapter\Pdo\Mysql
     */
    public function rollbackTransaction($event, $connection)
    {
        if ($this->_app->debug || $this->_app->profile) {
            $id = $connection->getConnectionId();
            $key = json_encode([
                'connection_id' => $id,
                'level' => $connection->getTransactionLevel()
            ]);
            $result = \app\common\events\Profiler::getInstance()->end($key);
            $this->_logger->notice('rollback transaction');
            if ($result) {
                $this->_logger->notice($key . '=>' . json_encode($result));
            }
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