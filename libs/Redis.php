<?php

namespace app\common\libs;

class Redis extends \Phalcon\Cache\Backend\Redis
{
    public $prefix = null;

    protected $_cache;

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
        $this->_cache[$key] = $content;
        return parent::save($key, $content, $lifetime, $stopBuffer);
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
    public function queryKeys($prefix = null) {}

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
}