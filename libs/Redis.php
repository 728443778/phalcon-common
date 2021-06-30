<?php

namespace app\core\libs;

use app\core\constant\ErrorCode;
use app\core\exceptions\RuntimeException;

class Redis extends \Phalcon\Cache\Backend\Redis
{
    public $prefix = null;

    protected $_cache = [];

    public function __construct($frontend, $options = null)
    {
        parent::__construct($frontend, $options);
        if (isset($options['prefix'])) {
            $this->prefix = $options['prefix'];
        }
    }

    public function getKey($key)
    {
        if (!$this->prefix) {
            return $key;
        }
        return $this->prefix . $key;
    }

    /**
     * Returns a cached content
     *
     * @param string $keyName
     * @param int $lifetime
     * @return mixed|null
     */
    public function get($keyName, $lifetime = null)
    {
        $key = $this->getKey($keyName);
        $data = parent::get($key, $lifetime);
        if ($data) {
            $this->_cache[$key] = $data;
        }
        return $data;
    }

    /**
     * Stores cached content into the file backend and stops the frontend
     *
     * <code>
     * $cache->save("my-key", $data);
     *
     * // Save data termlessly
     * $cache->save("my-key", $data, -1);
     * </code>
     *
     * @param int|string $keyName
     * @param string $content
     * @param int $lifetime
     * @param boolean $stopBuffer
     * @return bool
     */
    public function save($keyName = null, $content = null, $lifetime = null, $stopBuffer = true)
    {
        $key = $this->getKey($keyName);
        //        $this->_cache[$key] = $content;
        return parent::save($key, $content, $lifetime, $stopBuffer);
    }

    /**
     * 把一个东西存到本次回话中，主要用户mongodb查询，因为mongodb本来查询就比较快，而且mongodb查询的数据都比较大
     * 放到redis等缓存中，不显示，其实主要就是解决一些东西的重复查询问题
     * @param $keyName
     * @param $content
     */
    public function saveToSession($keyName, $content)
    {
        $this->_cache[$keyName] = $content;
    }

    /**
     * 获取存在于本次回话中的变量，这个东西不走cache服务
     * @param $keyName
     * @return null
     */
    public function getToSession($keyName)
    {
        if (array_key_exists($keyName, $this->_cache)) {
            return $this->_cache[$keyName];
        }
        return null;
    }

    /**
     * Deletes a value from the cache by its key
     *
     * @param int|string $keyName
     * @return bool
     */
    public function delete($keyName)
    {
        $key = $this->getKey($keyName);
        return parent::delete($key);
    }

    /**
     * Query the existing cached keys.
     *
     * <code>
     * $cache->save("users-ids", [1, 2, 3]);
     * $cache->save("projects-ids", [4, 5, 6]);
     *
     * var_dump($cache->queryKeys("users")); // ["users-ids"]
     * </code>
     *
     * @param string $prefix
     * @return array
     */
    public function queryKeys($prefix = null)
    {
    }

    /**
     * Checks if cache exists and it isn't expired
     *
     * @param string $keyName
     * @param int $lifetime
     * @return bool
     */
    public function exists($keyName = null, $lifetime = null)
    {
        $key = $this->getKey($keyName);
        return parent::exists($key, $lifetime);
    }

    /**
     * Increment of given $keyName by $value
     *
     * @param string $keyName
     * @param int $value
     * @return int
     */
    public function increment($keyName = null, $value = 1)
    {
        $key = $this->getKey($keyName);
        return parent::increment($key, $value);
    }

    /**
     * Decrement of $keyName by given $value
     *
     * @param string $keyName
     * @param int $value
     * @return int
     */
    public function decrement($keyName = null, $value = 1)
    {
        $key = $this->getKey($keyName);
        return parent::decrement($key, $value);
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function getCache($key)
    {
        $key = $this->getKey($key);
        if (isset($this->_cache[$key])) {
            return $this->_cache[$key];
        }
        $data = $this->get($key);
        if ($data) {
            $this->_cache[$key] = $data;
        }
        return $data;
    }

    /**
     * lock key in $ttl seconds
     * @param string $key
     * @param integer $ttl
     * @param bool $failedThorExce 失败是否抛出异常
     * @return  1 success
     * @return 0 failed
     * @throw app\core\exceptions\RuntimeException failed
     */
    public function lock($key, $ttl = 2, $failedThorExce = true)
    {
        if (empty($this->_redis)) {
            $this->_connect();
        }
        $ttl = (int) $ttl;
        $key = $this->getKey($key);
        $this->_redis->multi();    // 标记一个事务块的开始
        $this->_redis->incr($key, 1);
        $this->_redis->expire($key, $ttl);
        $res = $this->_redis->exec();
        if ($res[1] == true && $res[0] == 1) {
            return 1;
        } else {
            if ($failedThorExce) {
                throw new RuntimeException('lock failed', ErrorCode::RUNTIME_ERROR);
            }
            return 0;
        }
    }

    public function unlock($key)
    {
        if (empty($this->_redis)) {
            $this->_connect();
        }
        $key = $this->getKey($key);
        return $this->_redis->del($key);
    }
}
