<?php

namespace app\common\components;

use app\common\traits\Services;

class User extends \Phalcon\Di\Injectable
{
    use Services;

    public $token;

    public $uid;

    public $cache;

    /**
     * @var \app\models\User
     */
    public $user;

    public $last_request_at = 0;

    /**
     * @return string
     */
    public function genRandomString()
    {
        $bytes = $this->getRandom()->bytes();
        return bin2hex($bytes);
    }

    public function initUser($token)
    {
        $binData = hex2bin($token);
        $token = $this->crypt->decrypt($binData);
        $cache = $this->getCache();
        $data = $cache->get($token);
        if (!isset($data['uid'])) {
            return false;
        }
        $this->token = $token;
        foreach ($data as $key=>$value) {
            $this->user->$key = $value;
        }
        return true;
    }

}