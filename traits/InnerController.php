<?php

namespace app\core\traits;

use Phalcon\Http\Request\Exception;

/**
 * 只能使用与Phalcon 的controller
 * Class InnerController
 * @package app\traits
 */
trait InnerController
{
    public function initialize()
    {
        $clientAddr = $this->request->getClientAddress();
        $config = $this->getDI()->getConfig();
        $innerIp = preg_match($config->inner_ip_pattern, $clientAddr);
        if (!$innerIp) {
            $this->response->setStatusCode(403);
            throw new Exception('Forbidden', 403);
        }
    }
}