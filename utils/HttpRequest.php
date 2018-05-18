<?php

namespace app\common\utils;

use app\common\events\Profiler;
use app\common\libs\Application;

class HttpRequest extends \sevenUtils\HttpRequest
{
    protected $_logger;

    protected $url;

    protected $_returnArrayByJsonDecode = true;

    public function __construct()
    {
        parent::__construct();
        $this->_logger = Application::getApp()->logger;
    }

    public function beforeRequest($Url, $data = [])
    {
        $this->url = $Url;
        if (Application::getApp()->debug || Application::getApp()->profile) {
            ++Application::getApp()->_httpRequestCount;
            Profiler::getInstance()->start($Url);

            $this->_logger->notice('http reqeust:' . $Url);
            $this->_logger->notice('data:' . json_encode($data));
        }
    }

    public function afterRequest(&$response)
    {
        if (Application::getApp()->debug || Application::getApp()->profile) {
            $result = Profiler::getInstance()->end($this->url);
            Application::getApp()->_httpRequestTime += $result['use_time'];
            $logData = 'http end:' . $this->url . '=>' . json_encode($result);
            $this->_logger->notice($logData);
            $this->_logger->notice('response:' . $response);
        }
        if ($this->_returnArrayByJsonDecode) {
            $response = json_decode($response, true);
        }
        return $response;
    }
}