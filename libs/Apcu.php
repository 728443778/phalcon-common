<?php
/**
 * Created by 侯成华.
 * User: 侯成华
 * Date: 5/30/18
 * Time: 7:24 PM
 *
 * 因为在使用apcu时，如果失败phalcon会直接退出，这儿重写框架自带的apcu，因为如果失败了，下次重新拉曲数据就行了
 */

namespace app\common\libs;

class Apcu extends \Phalcon\Cache\Backend\Apcu
{
    /**
     * Stores cached content into the APCu backend and stops the frontend
     *
     * @param string|int keyName
     * @param string content
     * @param int lifetime
     * @param boolean stopBuffer
     * @return bool
     */
    public function save($keyName=null, $content=null, $lifetime=null, $stopBuffer=null)
    {
        $flag = parent::save($keyName, $content, $lifetime, $stopBuffer);
        if (!$flag) {
            Application::getApp()->getLogger()->debug('apcu save failed :' . $keyName);
        }
        return true;
    }


    /**
     * Increment of a given key, by number $value
     *
     * @param string keyName
     */
    public function increment($keyName=null, $value=null)
    {
        $flag = parent::increment($keyName, $value);
        if (!$flag) {
            Application::getApp()->getLogger()->debug('apcu increment failed :' . $keyName);
        }
        return true;
    }


    /**
     * Decrement of a given key, by number $value
     *
     * @param string keyName
     */
    public function decrement($keyName=null, $value=null)
    {
        $flag = parent::decrement($keyName, $value);
        if (!$flag) {
            Application::getApp()->getLogger()->debug('apcu decrement failed :' . $keyName);
        }
    }
}