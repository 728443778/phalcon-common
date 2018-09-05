<?php

namespace app\common\utils;

use app\common\events\Profiler;
use app\common\libs\Application;
use function MongoDB\is_string_array;

class HttpRequest extends \sevenUtils\HttpRequest
{
    protected $_logger;

    protected $url;

    protected $_returnArrayByJsonDecode = true;

    public function __construct()
    {
        $this->_logger = Application::getApp()->getLogger();
    }

    public function beforeRequest($Url, $data = [])
    {
        $this->_logger = Application::getApp()->getLogger();
        $this->url = $Url;
        if (Application::getApp()->debug || Application::getApp()->profile) {
            ++Application::getApp()->_httpRequestCount;
            Profiler::getInstance()->start($Url);

            $this->_logger->notice('http request:' . $Url);
            if (!is_string($data)) {
                $this->_logger->notice('json_encode_data:' . json_encode($data));
            } else {
                $this->_logger->notice('data:' . $data);

            }
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