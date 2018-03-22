<?php

namespace app\common\Logs;

use app\common\libs\Application;
use Phalcon\Logger\Adapter\Stream;

class FileLog extends Stream
{
    public function log($type, $message = null, array $context = null)
    {
        $logId = Application::getApp()->getLogId();
        $message = '{' . $logId . '} ' . $message;
        parent::log($type, $message, $context); // TODO: Change the autogenerated stub
    }
}