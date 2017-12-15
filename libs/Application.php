<?php

namespace app\common\libs;

use app\common\events\Profiler;
use app\common\traits\Services;
use Phalcon\DiInterface;
use sevenUtils\resources\Client;

class Application extends \Phalcon\Mvc\Application
{

    use Services;

    /**
     * @var \app\common\components\User
     */
    public $user;

    /**
     * @var Application
     */
    public static $app;

    public $requestId;

    protected $_loadFiles = [];

    public $_loadFileCount = 0;

    public $_dbCount = 0;

    public $_dbOpTime = 0;

    protected $_config;

    protected $_logId;

    public function setLoadFile($file)
    {
        if (!in_array($file, $this->_loadFiles)) {
            $this->_loadFiles[] = $file;
            ++$this->_loadFileCount;
        }
    }

    public function getLoadFiles()
    {
        return $this->_loadFiles;
    }

    public function __construct(DiInterface $dependencyInjector = null)
    {
        parent::__construct($dependencyInjector);
        $this->user = new \app\common\components\User();
        self::$app = $this;
        $config = $this->getConfig();
        if ($config->debug) {
            Profiler::getInstance()->start('RequestProfile');
            $time = $this->getRequestTime();
            $this->logger->notice('Request start:' . date('Y-m-d H:i:s', $time));
            $request = $this->request;
            $data = [
                'request_method' => $request->getMethod(),
                'get_param' => $_GET,
                'post_param' => $_POST,
                'headers' => $request->getHeaders(),
                'client_addrs' => $request->getClientAddress()
            ];
            $this->logger->info(json_encode($data));
        }
    }

    public function getRandNumber($length = 6)
    {
        $random = $this->getRandom();
        $captcha = '';
        for ($i = 0; $i < $length; ++$i) {
            $captcha .= $random->number(9);
        }
        return $captcha;
    }

    public function getRequestId()
    {
        if ($this->requestId) {
            return $this->requestId;
        }
        $this->requestId = $this->genRandomString(16);
        return $this->requestId;
    }

    public function getUUid()
    {
        return $this->getRandom()->uuid();
    }

    public function genRandomString($binBytes = 16)
    {
        $bytes = $this->getRandom()->bytes($binBytes);
        return bin2hex($bytes);
    }

    public function encrypt($string)
    {
        $bin = $this->crypt->encrypt($string);
        return bin2hex($bin);
    }

    public function decrypt($string)
    {
        $bin = hex2bin($string);
        return $this->crypt->decrypt($bin);
    }

    public function genToken($prefix)
    {
        $token = $this->genRandomString();
        return $prefix . '-' . $token;
    }

    public function getRequestTime()
    {
        $time = $this->request->getServer('REQUEST_TIME');
        if (!$time) {
            $time = $this->request->getServer('request_time');
            if (!$time) {
                $time = time();
                $_SERVER['REQUEST_TIME'] = $time;
            }
        }
        return $time;
    }

    /**
     * @return Client
     */
    public function getResourceClient()
    {
        return $this->getDI()->get('resources_client');
    }

    public static function getApp()
    {
        if (!static::$app) {
            static::$app = new static();
        }
        return static::$app;
    }

    public function getConfig($reGet = false)
    {
        if ($this->_config && !$reGet) {
            return $this->_config;
        }
        $this->_config = $this->getDI()->getConfig();
        return $this->_config;
    }

    public function getLogId()
    {
        if ($this->_logId) {
            return $this->_logId;
        }
        $date = date('YmdHis');
        $this->_logId = uniqid($date);
        return $this->_logId;
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $config = $this->getConfig();
        if ($config->debug) {
            $response = $this->response;
            $content = $response->getContent();
            $statusCode = $response->getStatusCode();
            if (!$statusCode) {
                $statusCode = 200;
            }
            $headerList = headers_list();
            $response = [
                'headers' => $headerList,
                'status' => $statusCode,
                'content' => $content,

            ];
            $this->logger->info('Response:' . json_encode($response));
            $this->logger->info('Autoload file count:' . $this->_loadFileCount);
            $dbMessage = 'mysql operate count:' . $this->_dbCount . '; time:' . $this->_dbOpTime;
            $this->logger->info($dbMessage);
            $result = Profiler::getInstance()->end('RequestProfile');
            $this->logger->debug(json_encode($result));
            $this->logger->notice('Request end');
        }
    }
}