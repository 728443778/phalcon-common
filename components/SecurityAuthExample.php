<?php

class SecurityAuthExample
{
    public function genAuthParams($authId, $authToken)
    {
        $time = time();
        $params = [
            'auth_id' => $authId,
            'auth_time' => $time,
            'auth_token' => hash('sha256', $authToken . $time)
        ];
        $return = \app\common\utils\HttpRequest::getInstance()->requestPost('http://dwadwa', $params);
        if (isset($return['code']) && $return['code'] == 0) {
            echo 'SUCCESS';
            return ;
        }
        echo \app\common\utils\HttpRequest::getInstance()->lastResponse;
    }
}