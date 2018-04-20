<?php


namespace app\common\traits;


use app\common\libs\Application;
use Phalcon\Http\Request;

trait SecurityAuth
{
    use ErrorCode;

    protected $authID;

    protected $authToken;

    protected $authTime;

    public function Auth(Request $request,\app\common\components\SecurityAuth $auth)
    {
        $this->authID = $request->getPost('auth_id');
        $this->authToken = $request->getPost('auth_token');
        $this->authTime = $request->getPost('auth_time');
        $time = Application::getApp()->getRequestTime();
        if (($this->authTime + 30) < $time) {
            self::$errorStr = 'auth time out';
            return false;
        }
        $client = $auth->findAuthClient($this->authID);
        if (!$client) {
            self::$errorStr = 'not found auth id';
            return false;
        }
        $token = hash('sha256', $client->token . $this->authTime);
        if ($token != $this->authToken) {
            self::$errorStr = 'auth failed';
            return false;
        }
        return true;
    }
}