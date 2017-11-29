<?php

namespace app\common\traits;

use app\common\libs\Redis;
use MongoDB\Client;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Security;
use Phalcon\Security\Random;

/**
 * 只能适用于继承了\Phalcon\Di\Injectable 的类
 * Class Services
 * @package app\traits
 */
trait Services
{
    /**
     * @return Random
     */
    public function getRandom($name = 'random')
    {
        return $this->getDI()->getShared($name);
    }

    /**
     * @return Security
     */
    public function getSecurity($name='security')
    {
        return $this->getDI()->getShared($name);
    }

    /**
     * @return Redis
     */
    public function getCache($name='redis')
    {
        return $this->getDI()->getShared($name);
    }

    /**
     * @param string $name
     * @return Mysql
     */
    public function getPdoConnection($name = 'db')
    {
        return $this->getDI()->getShared($name);
    }

    /**
     * @param string $name
     * @return Client
     */
    public function getMongodbConnection($name = 'mongodb')
    {
        return $this->getDI()->getShared($name);
    }
}