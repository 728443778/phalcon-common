<?php
/**
 * Created by 侯成华.
 * User: 侯成华
 * Date: 5/30/18
 * Time: 7:24 PM
 *
 * 因为在使用apcu时，如果失败phalcon会直接退出，这儿重写框架自带的apcu，因为如果失败了，下次重新拉曲数据就行了
 */

namespace app\core\libs;

class Apcu extends \Phalcon\Cache\Backend\Apcu
{

    /**
     * Stores cached content into the APCu backend and stops the frontend
     *
     * @param string|int $keyName
     * @param string $content
     * @param int $lifetime
     * @param boolean $stopBuffer
     * @return bool
     */
    public function save($keyName=null, $content=null, $lifetime=null, $stopBuffer=null)
    {
        try {
            $flag = parent::save($keyName, $content, $lifetime, $stopBuffer);
            if (!$flag) {
            Application::getApp()->getLogger()->debug('apcu save failed :' . $keyName);
        }
        } catch (\Exception $exception) {
            Application::getApp()->getLogger()->debug('apcu save failed :' . $keyName. ',Exception is:' . $exception->getMessage());
        }
        return true;
    }


    /**
     * Increment of a given key, by number $value
     *
     * @param string $keyName
     * @param float $value
     * @return bool
     */
    public function increment($keyName=null, $value=1)
    {
        try {
            $flag = parent::increment($keyName, $value);
            if (!$flag) {
                Application::getApp()->getLogger()->debug('apcu increment failed :' . $keyName);
            }
        } catch (\Exception $exception) {
            Application::getApp()->getLogger()->debug('apcu save increment :' . $keyName. ',Exception is:' . $exception->getMessage());
        }
        return true;
    }


    /**
     * Decrement of a given key, by number $value
     *
     * @param string $keyName
     * @return  bool
     */
    public function decrement($keyName=null, $value=1)
    {
        try {
            $flag = parent::decrement($keyName, $value);
            if (!$flag) {
                Application::getApp()->getLogger()->debug('apcu decrement failed :' . $keyName);
            }
        } catch (\Exception $exception) {
            Application::getApp()->getLogger()->debug('apcu save decrement :' . $keyName. ',Exception is:' . $exception->getMessage());
        }
        return true;
    }
}