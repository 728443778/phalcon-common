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
     * 用户实例
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

}