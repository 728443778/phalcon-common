<?php

namespace app\common\collections;

use app\common\events\Profiler;
use app\common\libs\Application;
use MongoDB\Client;
use MongoDB\Database;

/**
 * 该类只适用于Phalcon框架
 * Class Collection
 * @package app\collections
 */
class Collection extends \Phalcon\Di\Injectable
{
    /**
     * @var Client
     */
    protected $_connectionServices;

    /**
     * @var Database
     */
    protected $_databaseName;

    /**
     * @var \MongoDB\Collection
     */
    protected $_collection;

    protected $_connectionName;

    protected $_collectionName;

    /**
     * Collection constructor.
     * @param $colleciontName string 集合的名字
     * @param null $databaseName string 数据库的名字 不填，则使用配置文件的
     * @param null $connectionName mongodb的链接名字，不选，则使用默认的
     * @throws \MongoConnectionException
     * @throws \MongoException
     */
    public function __construct($colleciontName, $databaseName = null, $connectionName = null)
    {
        if ($connectionName == null) {
            $connectionName = 'mongodb';
        }
        $flag = $this->selectConnection($connectionName);
        if (!$flag) {
            throw new \MongoConnectionException('get mongodb connection failed', ERROR_GET_MONGODB_CONNECTION_FAILED);
        }
        $flag = $this->selectCollection($colleciontName, $databaseName);
        if (!$flag) {
            throw new \MongoException('select mongodb collection failed', ERROR_SELECT_MONGODB_COLLECTION_FAILED);
        }
    }

    public function selectConnection($connectionname)
    {
        $connection = $this->getDI()->getShared($connectionname);
        if (!$connection) {
            return false;
        }
        $this->_connectionName = $connectionname;
        $this->_connectionServices = $connection;
        return $this;
    }

    public function selectCollection($collectionName, $databaseName = null)
    {
        if ($databaseName === null) {
            $name = $this->_connectionName;
            $databaseName = $this->getDI()->getConfig()->$name->database;
        }
        $this->_databaseName = $databaseName;
        $this->_collectionName = $collectionName;
        $this->_collection = $this->_connectionServices->selectCollection($this->_databaseName, $this->_collectionName);
        if (!$this->_collection) {
            return false;
        }
        return $this;
    }

    /**
     * 删除一条匹配到的数据集
     * @param $filter
     * @param $options
     * @return bool|\MongoDB\DeleteResult
     */
    public function deleteOne($filter, $options)
    {
        if (Application::getApp()->debug || Application::getApp()->profile) {
            Profiler::getInstance()->start('collection_delete_one' . json_encode($filter));
        }
        $result = $this->_collection->deleteOne($filter, $options);
        if (Application::getApp()->debug || Application::getApp()->profile) {
            $profierResult = Profiler::getInstance()->end('collection_delete_one' . json_encode($filter));
            ++Application::getApp()->_collectionCount;
            Application::getApp()->_collectionTime += $profierResult['use_time'];
            $profierResult['where'] = $filter;
            Application::getApp()->getLogger()->notice('collection_delete_one' . json_encode($profierResult));
        }
        if ($result->getDeletedCount() < 1) {
            return false;
        }
        return $result;
    }

    /**
     * 删除匹配到的所有数据集
     * @param $filter
     * @param $options
     * @return bool|\MongoDB\DeleteResult
     */
    public function deleteMany($filter, $options)
    {
        if (Application::getApp()->debug || Application::getApp()->profile) {
            Profiler::getInstance()->start('collection_delete_many' . json_encode($filter));
        }
        $result = $this->_collection->deleteMany($filter, $options);
        if (Application::getApp()->debug || Application::getApp()->profile) {
            $profierResult = Profiler::getInstance()->end('collection_delete_many' . json_encode($filter));
            ++Application::getApp()->_collectionCount;
            Application::getApp()->_collectionTime += $profierResult['use_time'];
            $profierResult['where'] = $filter;
            Application::getApp()->getLogger()->notice('collection_delete_many' . json_encode($profierResult));
        }
        if ($result->getDeletedCount() < 1) {
            return false;
        }
        return $result;
    }


    /**
     * @param $key ['username' => 1];
     * @param array $options ['unique' => 1]
     * @return $this|bool
     */
    public function createIndex($key, $options = [])
    {
        $result = $this->_collection->createIndex($key, $options);
        if (!$result || empty($result)) {
            return false;
        }
        return $this;
    }

    /**
     * @param $keys  = [
     *         [ 'key' => [ 'username' => 1 ], 'unique' => true ],
     *         // Create a 2dsphere index on the "loc" field with a custom name
     *         [ 'key' => [ 'loc' => '2dsphere' ], 'name' => 'geo' ],
     *     ];
     * @param array $options
     */
    public function createIndexes($keys, $options = [])
    {
        $result = $this->_collection->createIndexes($keys, $options);
        if (!$result || empty($result)) {
            return false;
        }
        return $this;
    }

    /**
     * @param $data
     * @param array $options
     * @return bool|\MongoDB\InsertManyResult|\MongoDB\InsertOneResult
     */
    public function insert($data, $options = [])
    {
        if (is_array($data) && isset($data[0])) {
            return $this->insertMany($data, $options);
        }
        return $this->insertOne($data, $options);
    }

    /**
     * @param $data array | object
     * @param array $options
     * @return bool|\MongoDB\InsertOneResult
     */
    public function insertOne($data, $options = [])
    {
        if (Application::getApp()->debug || Application::getApp()->profile) {
            Profiler::getInstance()->start('collection_insert_one' . json_encode($data));
        }
        $result = $this->_collection->insertOne($data, $options);
        if (Application::getApp()->debug || Application::getApp()->profile) {
            $profierResult = Profiler::getInstance()->end('collection_insert_one' . json_encode($data));
            ++Application::getApp()->_collectionCount;
            Application::getApp()->_collectionTime += $profierResult['use_time'];
            $profierResult['data'] = $data;
            Application::getApp()->getLogger()->notice('collection_insert_one' . json_encode($profierResult));
        }
        if ($result->getInsertedCount() < 1) {
            return false;
        }
        return $result;
    }

    /**
     * @param $datas array | object[]
     * @param array $options
     * @return bool|\MongoDB\InsertManyResult
     */
    public function insertMany($datas, $options = [])
    {
        if (Application::getApp()->debug || Application::getApp()->profile) {
            Profiler::getInstance()->start('collection_insert_maney' . json_encode($datas));
        }
        $result = $this->_collection->insertMany($datas, $options);
        if (Application::getApp()->debug || Application::getApp()->profile) {
            $profierResult = Profiler::getInstance()->end('collection_insert_maney' . json_encode($datas));
            ++Application::getApp()->_collectionCount;
            Application::getApp()->_collectionTime += $profierResult['use_time'];
            $profierResult['datas'] = $datas;
            Application::getApp()->getLogger()->notice('collection_insert_maney' . json_encode($profierResult));
        }
        if ($result->getInsertedCount() < 1) {
            return false;
        }
        return $result;
    }

    /**
     * 从匹配到的数据中修改一条数据
     * @param $filter
     * @param $update
     * @param array $options
     * @return bool|\MongoDB\UpdateResult
     */
    public function updateOne($filter, $update, $options = [])
    {
        if (Application::getApp()->debug || Application::getApp()->profile) {
            Profiler::getInstance()->start('collection_update_one' . json_encode($filter));
        }
        $result = $this->_collection->updateOne($filter, $update, $options);
        if (Application::getApp()->debug || Application::getApp()->profile) {
            $profierResult = Profiler::getInstance()->end('collection_update_one' . json_encode($filter));
            ++Application::getApp()->_collectionCount;
            Application::getApp()->_collectionTime += $profierResult['use_time'];
            $profierResult['where'] = $filter;
            Application::getApp()->getLogger()->notice('collection_update_one' . json_encode($profierResult));
        }
        if ($result->getModifiedCount() < 1) {
            return false;
        }
        return $result;
    }

    /**
     * 修改匹配到的所有数据集
     * @param $filter
     * @param $update
     * @eg 更新数组中的指定元素，比如 countries是一个数 $update = ['$set' => ['countries.1.name' => 'china']]; //把countries索引为1的元素的name改为china
     * @param array $options
     * @return bool|\MongoDB\UpdateResult
     */
    public function updateMany($filter, $update, $options = [])
    {
        if (Application::getApp()->debug || Application::getApp()->profile) {
            Profiler::getInstance()->start('collection_update_many' . json_encode($filter));
        }
        $result = $this->_collection->updateMany($filter, $update, $options);
        if (Application::getApp()->debug || Application::getApp()->profile) {
            $profierResult = Profiler::getInstance()->end('collection_update_many' . json_encode($filter));
            ++Application::getApp()->_collectionCount;
            Application::getApp()->_collectionTime += $profierResult['use_time'];
            $profierResult['where'] = $filter;
            Application::getApp()->getLogger()->notice('collection_update_many' . json_encode($profierResult));
        }
        if($result->getModifiedCount() < 1) {
            return false;
        }
        return $result;
    }

    public function getAllIndexes($options = [])
    {
        return $this->_collection->listIndexes($options);
    }

    /**
     * 查找一条数据集
     * @param array $filter
     * @param array $options
     * @return array|null|object
     */
    public function findOne($filter = [], $options = [])
    {
        if (Application::getApp()->debug || Application::getApp()->profile) {
            Profiler::getInstance()->start('collection_find_one' . json_encode($filter));
        }
        $result = $this->_collection->findOne($filter, $options);
        if (Application::getApp()->debug || Application::getApp()->profile) {
            $profierResult = Profiler::getInstance()->end('collection_find_one' . json_encode($filter));
            ++Application::getApp()->_collectionCount;
            Application::getApp()->_collectionTime += $profierResult['use_time'];
            $profierResult['where'] = $filter;
            Application::getApp()->getLogger()->notice('collection_find_one' . json_encode($profierResult));
        }
        return $result;
    }

    /**
     * @param array $filter
     * @param array $options
     * @eg 只查询某些字段 $options = ['projects' => ['_id' => 1,'status'=>1]]; //返回_id,status
     * @eg 在数组中查询某个索引的字段的值 $filter = ['countries.0.name' => 'china'];
     * @eg 在数组中查询某个字段等于某个值的所有数据  $filter = ['countries' => ['$elemMatch' => ['name' => 'china']
     * @return \MongoDB\Driver\Cursor
     */
    public function find($filter = [], $options = [])
    {
        if (Application::getApp()->debug || Application::getApp()->profile) {
            Profiler::getInstance()->start('collection_find' . json_encode($filter));
        }
        $result = $this->_collection->find($filter, $options);
        if (Application::getApp()->debug || Application::getApp()->profile) {
            $profierResult = Profiler::getInstance()->end('collection_find' . json_encode($filter));
            ++Application::getApp()->_collectionCount;
            Application::getApp()->_collectionTime += $profierResult['use_time'];
            $profierResult['where'] = $filter;
            Application::getApp()->getLogger()->notice('collection_find' . json_encode($profierResult));
        }
        return $result;
    }

    /**
     * @param $filter
     * @param $replaceData
     * @param array $options
     * @return array|null|object
     * @throws if failed
     */
    public function findOneReplace($filter, $replaceData, $options = [])
    {
        if (Application::getApp()->debug || Application::getApp()->profile) {
            Profiler::getInstance()->start('collection_findOneReplace' . json_encode($filter));
        }
        $return = $this->_collection->findOneAndReplace($filter, $replaceData, $options);
        if (Application::getApp()->debug || Application::getApp()->profile) {
            $profierResult = Profiler::getInstance()->end('collection_findOneReplace' . json_encode($filter));
            ++Application::getApp()->_collectionCount;
            Application::getApp()->_collectionTime += $profierResult['use_time'];
            $profierResult['where'] = $filter;
            Application::getApp()->getLogger()->notice('collection_findOneReplace' . json_encode($profierResult));
        }
        return $return;
    }

    /**
     * @param $indexName
     * @param array $options
     * @return array|object
     * @throws if failed
     */
    public function dropIndex($indexName, $options = [])
    {
        return $this->_collection->dropIndex($indexName, $options);
    }

    /**
     * @param array $options
     * @return array|object
     * @throws  if failed
     */
    public function dropAllIndexes($options = [])
    {
        return $this->_collection->dropIndexes($options);
    }

    /**
     * @param array $options
     * @return array|object
     */
    public function drop($options = [])
    {
        return $this->_collection->drop($options);
    }

    /**
     * @return \MongoDB\Collection
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * @param \Phalcon\Mvc\CollectionInterface $model
     * @return Client
     */
    public function getMongoDBConnection()
    {
        return $this->_connectionServices;
    }
}