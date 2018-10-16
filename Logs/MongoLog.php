<?php

namespace app\core\Logs;

use app\core\collections\Collection;
use app\core\libs\Application;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\Stream;

class MongoLog extends Stream
{
    protected $_mongodb;

    protected $_collectionName = 'app_run_log';

    protected $_databaseName = null;

    protected $_connectionName = null;

    protected $_logId;

    public function __construct($name, $options = null)
    {
        $this->_collectionName = $name;
        if (isset($options['connection'])) {
            $this->_connectionName = $options['connection'];
            unset($options['connection']);
        }
        if (isset($options['database'])) {
            $this->_databaseName = $options['database'];
            unset($options['database']);
        }
        $this->_mongodb = new Collection($this->_collectionName, $this->_databaseName, $this->_connectionName);
//        parent::__construct($name, $options);
        $this->_logId = Application::getApp()->getLogId();
        $this->_mongodb->createIndex(['log_id' => 1], ['unique' => true]);
    }

    public function setLogId($value)
    {
        $this->_logId = $value;
    }

    public function log($type, $message = null, array $context = null)
    {
        if ($type > $this->_logLevel) {
            return;
        }
        switch ($type) {
            case Logger::ERROR:
                $type = 'error';
                break;
            case Logger::SPECIAL:
                $type = 'special';
                break;
            case Logger::INFO:
                $type = 'info';
                break;
            case Logger::CRITICAL:
                $type = 'critical';
                break;
            case Logger::CUSTOM:
                $type = 'custom';
                break;
            case Logger::ALERT:
                $type = 'alert';
                break;
            case Logger::EMERGENCE:
                $type = 'emergence';
                break;
            case Logger::NOTICE:
                $type = 'notice';
                break;
            case Logger::WARNING:
                $type = 'warning';
                break;
            case Logger::DEBUG:
                $type = 'debug';
                break;

        }
        $this->_mongodb->updateOne(['log_id' => $this->_logId], [
            '$push' => [
                'log' => [
                    'type' => $type,
                    'data' => $message
                ],
            ],
        ], [
            'upsert' => true
        ]);
    }
}