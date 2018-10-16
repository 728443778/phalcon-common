<?php
/**
 * Created by 侯成华.
 * User: 侯成华
 * Date: 2018/5/31
 * Time: 上午10:25
 */

namespace app\core\libs;

class Yac extends \Phalcon\Cache\Backend
{
    protected $yac;

    public function __construct(\Phalcon\Cache\FrontendInterface $frontend, $options=null)
    {
        parent::__construct($frontend, $options);
        $this->yac = new \Yac();
    }

    public function get($keyName, $lifetime=null)
    {
        return $this->yac->get($keyName);
    }


    public function save($keyName=null, $content=null, $lifetime=null, $stopBuffer=null)
    {
        return $this->yac->set($keyName, $content, $lifetime);
    }


    public function delete($keyName)
    {
        return $this->yac->delete($keyName);
    }


    public function queryKeys($prefix=null)
    {
        return $prefix;
    }


    public function exists($keyName=null, $lifetime=null)
    {

    }
}