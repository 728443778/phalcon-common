<?php

namespace app\core\components;


use Phalcon\Di\Injectable;

class SecurityAuth extends Injectable
{
    public function findAuthClient($authId)
    {
        $config = $this->getDI()->getShared('config');
        if (!property_exists($config, 'securities_auth')) {
            return false;
        }
        $securities = $config->securities_auth;
        foreach ($securities as $value) {
            if ($value->auth_id == $authId) {
                return $value;
            }
        }
        return false;
    }
}