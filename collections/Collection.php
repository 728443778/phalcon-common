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
    public function deleteOne($filter, $options = [])
    {
        if (Application::getApp()->debug || Application::getApp()->profile) {
            Profiler::getInstance()->start('collection_delete_one');
        }
        $result = $this->_collection->deleteOne($filter, $options);
        if (Application::getApp()->debug || Application::getApp()->profile) {
            $profierResult = Profiler::getInstance()->end('collection_delete_one');
            ++Application::getApp()->_collectionCount;
            Application::getApp()->_collectionTime += $profierResult['use_time'];
            $profierResult['where'] = $filter;
            $profierResult['options'] = $options;
            $collectionName = $this->_collection->getCollectionName();
            $dbs = $this->_collection->getDatabaseName();
            Application::getApp()->getLogger()->notice("{$dbs}.{$collectionName}.deleteOne:" . json_encode($profierResult));
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
            Profiler::getInstance()->start('collection_delete_many');
        }
        $result = $this->_collection->deleteMany($filter, $options);
        if (Application::getApp()->debug || Application::getApp()->profile) {
            $profierResult = Profiler::getInstance()->end('collection_delete_many');
            ++Application::getApp()->_collectionCount;
            Application::getApp()->_collectionTime += $profierResult['use_time'];
            $profierResult['where'] = $filter;
            $profierResult['options'] = $options;
            $collectionName = $this->_collection->getCollectionName();
            $dbs = $this->_collection->getDatabaseName();
            Application::getApp()->getLogger()->notice("{$dbs}.{$collectionName}.deleteMany:" . json_encode($profierResult));
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
        if (Application::getApp()->debug || Application::getApp()->profile) {
            Profiler::getInstance()->start('createIndex');
        }
        if (Application::getApp()->debug || Application::getApp()->profile) {
            $profierResult = Profiler::getInstance()->end('createIndex');
            ++Application::getApp()->_collectionCount;
            Application::getApp()->_collectionTime += $profierResult['use_time'];
            $profierResult['index'] = $key;
            $profierResult['options'] = $options;
            $collectionName = $this->_collection->getCollectionName();
            $dbs = $this->_collection->getDatabaseName();
            Application::getApp()->getLogger()->notice("{$dbs}.{$collectionName}.createIndex:" . json_encode($profierResult));
        }
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
        if (Application::getApp()->debug || Application::getApp()->profile) {
            Profiler::getInstance()->start('createIndexes');
        }
        $result = $this->_collection->createIndexes($keys, $options);
        if (Application::getApp()->debug || Application::getApp()->profile) {
            $profierResult = Profiler::getInstance()->end('createIndexes');
            ++Application::getApp()->_collectionCount;
            Application::getApp()->_collectionTime += $profierResult['use_time'];
            $profierResult['indexes'] = $keys;
            $profierResult['options'] = $options;
            $collectionName = $this->_collection->getCollectionName();
            $dbs = $this->_collection->getDatabaseName();
            Application::getApp()->getLogger()->notice("{$dbs}.{$collectionName}.createIndexes" . json_encode($profierResult));
        }
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
            Profiler::getInstance()->start('collection_insert_one');
        }
        $result = $this->_collection->insertOne($data, $options);
        if (Application::getApp()->debug || Application::getApp()->profile) {
            $profierResult = Profiler::getInstance()->end('collection_insert_one');
            ++Application::getApp()->_collectionCount;
            Application::getApp()->_collectionTime += $profierResult['use_time'];
            $profierResult['data'] = $data;
            $profierResult['options'] = $options;
            $collectionName = $this->_collection->getCollectionName();
            $dbs = $this->_collection->getDatabaseName();
            Application::getApp()->getLogger()->notice("{$dbs}.{$collectionName}.insertOne:" . json_encode($profierResult));
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
            Profiler::getInstance()->start('collection_insert_maney');
        }
        $result = $this->_collection->insertMany($datas, $options);
        if (Application::getApp()->debug || Application::getApp()->profile) {
            $profierResult = Profiler::getInstance()->end('collection_insert_maney');
            ++Application::getApp()->_collectionCount;
            Application::getApp()->_collectionTime += $profierResult['use_time'];
            $profierResult['datas'] = $datas;
            $profierResult['options'] = $options;
            $collectionName = $this->_collection->getCollectionName();
            $dbs = $this->_collection->getDatabaseName();
            Application::getApp()->getLogger()->notice("{$dbs}.{$collectionName}.insertMany:" . json_encode($profierResult));
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
            Profiler::getInstance()->start('collection_update_one');
        }
        $result = $this->_collection->updateOne($filter, $update, $options);
        if (Application::getApp()->debug || Application::getApp()->profile) {
            $profierResult = Profiler::getInstance()->end('collection_update_one');
            ++Application::getApp()->_collectionCount;
            Application::getApp()->_collectionTime += $profierResult['use_time'];
            $profierResult['where'] = $filter;
            $profierResult['options'] = $options;
            $collectionName = $this->_collection->getCollectionName();
            $dbs = $this->_collection->getDatabaseName();
            Application::getApp()->getLogger()->notice("{$dbs}.{$collectionName}.updateOne:" . json_encode($profierResult));
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
            Profiler::getInstance()->start('collection_update_many');
        }
        $result = $this->_collection->updateMany($filter, $update, $options);
        if (Application::getApp()->debug || Application::getApp()->profile) {
            $profierResult = Profiler::getInstance()->end('collection_update_many');
            ++Application::getApp()->_collectionCount;
            Application::getApp()->_collectionTime += $profierResult['use_time'];
            $profierResult['where'] = $filter;
            $profierResult['options'] = $options;
            $collectionName = $this->_collection->getCollectionName();
            $dbs = $this->_collection->getDatabaseName();
            Application::getApp()->getLogger()->notice("{$dbs}.{$collectionName}.updateMany:" . json_encode($profierResult));
        }
        if($result->getModifiedCount() < 1) {
            return false;
        }
        return $result;
    }

    public function getAllIndexes($options = [])
    {
        if (Application::getApp()->debug || Application::getApp()->profile) {
            Profiler::getInstance()->start('getAllIndexes');
        }
        $result = $this->_collection->listIndexes($options);
        if (Application::getApp()->debug || Application::getApp()->profile) {
            $profierResult = Profiler::getInstance()->end('getAllIndexes');
            ++Application::getApp()->_collectionCount;
            Application::getApp()->_collectionTime += $profierResult['use_time'];
            $collectionName = $this->_collection->getCollectionName();
            $dbs = $this->_collection->getDatabaseName();
            Application::getApp()->getLogger()->notice("{$dbs}.{$collectionName}.getAllIndexes:" . json_encode($profierResult));
        }
        return $result;
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
            Profiler::getInstance()->start('collection_find_one');
        }
        $result = $this->_collection->findOne($filter, $options);
        if (Application::getApp()->debug || Application::getApp()->profile) {
            $profierResult = Profiler::getInstance()->end('collection_find_one');
            ++Application::getApp()->_collectionCount;
            Application::getApp()->_collectionTime += $profierResult['use_time'];
            $profierResult['where'] = $filter;
            $profierResult['options'] = $options;
            $collectionName = $this->_collection->getCollectionName();
            $dbs = $this->_collection->getDatabaseName();
            Application::getApp()->getLogger()->notice("{$dbs}.{$collectionName}.findOne:" . json_encode($profierResult));
        }
        return $result;
    }

    /**
     * @param array $filter
     * @param array $options
     * @eg 只查询某些字段 $options = ['projection' => ['_id' => 1,'status'=>1]]; //返回_id,status
     * @eg 在数组中查询某个索引的字段的值 $filter = ['countries.0.name' => 'china'];
     * @eg 在数组中查询某个字段等于某个值的所有数据  $filter = ['countries' => ['$elemMatch' => ['name' => 'china']
     * @return \MongoDB\Driver\Cursor
     */
    public function find($filter = [], $options = [])
    {
        if (Application::getApp()->debug || Application::getApp()->profile) {
            Profiler::getInstance()->start('collection_find');
        }
        $result = $this->_collection->find($filter, $options);
        if (Application::getApp()->debug || Application::getApp()->profile) {
            $profierResult = Profiler::getInstance()->end('collection_find');
            ++Application::getApp()->_collectionCount;
            Application::getApp()->_collectionTime += $profierResult['use_time'];
            $profierResult['where'] = $filter;
            $profierResult['options'] = $options;
            $collectionName = $this->_collection->getCollectionName();
            $dbs = $this->_collection->getDatabaseName();
            Application::getApp()->getLogger()->notice("{$dbs}.{$collectionName}.find:". json_encode($profierResult));
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
            Profiler::getInstance()->start('collection_findOneReplace');
        }
        $return = $this->_collection->findOneAndReplace($filter, $replaceData, $options);
        if (Application::getApp()->debug || Application::getApp()->profile) {
            $profierResult = Profiler::getInstance()->end('collection_findOneReplace');
            ++Application::getApp()->_collectionCount;
            Application::getApp()->_collectionTime += $profierResult['use_time'];
            $profierResult['where'] = $filter;
            $profierResult['replaceData'] = $replaceData;
            $profierResult['options'] = $options;
            $collectionName = $this->_collection->getCollectionName();
            $dbs = $this->_collection->getDatabaseName();
            Application::getApp()->getLogger()->notice("{$dbs}.{$collectionName}.findOneReplace:" . json_encode($profierResult));
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

        if (Application::getApp()->debug || Application::getApp()->profile) {
            Profiler::getInstance()->start('dropIndex');
        }
        $return =  $this->_collection->dropIndex($indexName, $options);
        if (Application::getApp()->debug || Application::getApp()->profile) {
            $profierResult = Profiler::getInstance()->end('dropIndex');
            ++Application::getApp()->_collectionCount;
            Application::getApp()->_collectionTime += $profierResult['use_time'];
            $collectionName = $this->_collection->getCollectionName();
            $profierResult['index'] = $indexName;
            $profierResult['options'] = $options;
            $dbs = $this->_collection->getDatabaseName();
            Application::getApp()->getLogger()->notice("{$dbs}.{$collectionName}.dropIndex:" . json_encode($profierResult));
        }
        return $return;
    }

    /**
     * @param array $options
     * @return array|object
     * @throws  if failed
     */
    public function dropAllIndexes($options = [])
    {
        if (Application::getApp()->debug || Application::getApp()->profile) {
            Profiler::getInstance()->start('dropAllIndexes');
        }
        $return = $this->_collection->dropIndexes($options);
        if (Application::getApp()->debug || Application::getApp()->profile) {
            $profierResult = Profiler::getInstance()->end('dropAllIndexes');
            ++Application::getApp()->_collectionCount;
            Application::getApp()->_collectionTime += $profierResult['use_time'];
            $collectionName = $this->_collection->getCollectionName();
            $dbs = $this->_collection->getDatabaseName();
            Application::getApp()->getLogger()->notice("{$dbs}.{$collectionName}.dropAllIndexes:" . json_encode($profierResult));
        }
        return $return;
    }

    /**
     * @param array $options
     * @return array|object
     */
    public function drop($options = [])
    {
        if (Application::getApp()->debug || Application::getApp()->profile) {
            Profiler::getInstance()->start('drop');
        }
        $return = $this->_collection->drop($options);
        if (Application::getApp()->debug || Application::getApp()->profile) {
            $profierResult = Profiler::getInstance()->end('drop');
            ++Application::getApp()->_collectionCount;
            Application::getApp()->_collectionTime += $profierResult['use_time'];
            $collectionName = $this->_collection->getCollectionName();
            $dbs = $this->_collection->getDatabaseName();
            Application::getApp()->getLogger()->notice("{$dbs}.{$collectionName}.drop:" . json_encode($profierResult));
        }
        return $return;
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