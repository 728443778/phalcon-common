<?php

namespace app\common\libs;

class Sms
{
    public $errorMsg;

    public $tarGet;

    public $mobile;

    public $text;

    public $login;

    public $pass;

    public $url;

    /**
     * 发送给api需要的加密过后的pass
     * @var string
     */
    public $sendPass;

    public function __construct($url = null, $target = null, $login = null, $pass = null)
    {
        $config = Application::$app->getDI()->getConfig();
        if ($target == null) {
            $target = $config->sms->target;
        }
        if ($login == null) {
            $login = $config->sms->login;
        }
        if ($pass == null) {
            $pass = $config->sms->pass;
        }
        if ($url == null) {
            $url = $config->sms->url;
        }
        $this->tarGet = $target;
        $this->login = $login;
        $this->pass = $pass;
        $this->url = $url;
        $this->init();
    }

    public function init()
    {

    }

    public function sendMsg($mobile, $msg)
    {
        $this->mobile = $mobile;
        $this->sendPass = md5($this->mobile . $this->pass);
        $ch = curl_init($this->url . '?target='.$this->tarGet.'&msisdn='.$this->mobile.'&text='.$msg.'&login='.$this->login.'&pass='.$this->sendPass);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($response === false || $status != 200) {
            $this->errorMsg = json_encode([
                'status' => $status,
                'response' => $response,
                'curl_error' => curl_strerror(curl_errno($ch))
            ]);
            curl_close($ch);
            return false;
        }
        curl_close($ch);
        return true;
    }
}
