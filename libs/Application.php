<?php

namespace app\common\libs;

use app\common\events\Profiler;
use app\common\traits\Services;
use Phalcon\DiInterface;
use sevenUtils\resources\Client;

class Application extends \Phalcon\Mvc\Application
{

    use Services;

    public $debug;

    public $profile;

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

    public $_httpRequestCount = 0;

    public $_httpRequestTime = 0;

    public $_dbCount = 0;

    public $_dbOpTime = 0;

    public $_collectionCount = 0;

    public $_collectionTime = 0;

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
        if (property_exists($config, 'debug')) {
            $this->debug = boolval($config->debug);
        } else {
            $this->debug = false;
        }
        if (property_exists($config, 'profile')) {
            $this->profile = boolval($config->profile);
        } else {
            $this->profile = false;
        }
        $this->debug = $config->debug;
        $this->profile = $config->profile;
        if (($config->debug  || $config->profile)&& defined('MVC')) {
            Profiler::getInstance()->start('RequestProfile');
            $time = $this->getRequestTime();
            $this->logger->info('Request start:' . date('Y-m-d H:i:s', $time));

            $request = $this->request;
            $data = [
                'request_method' => $request->getMethod(),
                'get_param' => $_GET,
                'post_param' => $_POST,
                'origin_body_data' => file_get_contents('php://input'),
                'headers' => $request->getHeaders(),
                'client_address' => $request->getClientAddress(true),
                'x-forward-for' => $request->getHeader('X-Forwarded-For'),
                'x-ip' => $this->getRequestIp(),
            ];
            $this->logger->info(json_encode($data));
        }
    }

    public function getRequestIp()
    {
        $xip = $this->request->getHeader('X-Real-Ip');
        if (empty($xip)) {
            $xip = $this->request->getClientAddress(true);
        }
        return $xip;
    }

    public function getUserAgent()
    {
        return $this->request->getUserAgent();
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
        $requestTime = $this->getRequestTime();
        $str = (string)dechex($requestTime);
        $this->requestId = $str . $this->genRandomString(6);
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
        if (!empty($_SERVER['REQUEST_TIME'])) {
            return $_SERVER['REQUEST_TIME'];
        }
        $time = $this->getCache()->getToSession('REQUEST_TIME');
        if (empty($time)) {
            $time = time();
            $this->getCache()->saveToSession('REQUEST_TIME', $time);
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

    public static function getApp($di = null)
    {
        if (!static::$app) {
            static::$app = new static();
        }
        if ($di) {
            static::$app->setDI($di);
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

    /**
     * @return string
     */
    public function getLogId()
    {
        if ($this->_logId) {
            return $this->_logId;
        }
        $this->_logId = $this->getRequestId();
        return $this->_logId;
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        if (($this->debug || $this->profile) && defined('MVC')) {
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
                'body' => $content,

            ];
            $this->logger->info('Response:' . json_encode($response));
            $this->logger->info('Autoload file count:' . $this->_loadFileCount);
            if ($this->_dbCount != 0) {
                $dbMessage = 'mysql operate count:' . $this->_dbCount . '; time:' . $this->_dbOpTime;
                $this->logger->info($dbMessage);
            }

            if ($this->_httpRequestCount != 0) {
                $httpMessage = 'http operate count:' . $this->_httpRequestCount . '; use time:' . $this->_httpRequestTime;
                $this->logger->info($httpMessage);
            }
            if ($this->_collectionCount != 0) {
                $collectionMessage = 'collection operate count:' . $this->_collectionCount .'; use time:' . $this->_collectionTime;
                $this->logger->info($collectionMessage);
            }
            $result = Profiler::getInstance()->end('RequestProfile');
            $this->logger->info(json_encode($result));
            $this->logger->info('Request end');
        }
    }

    public function getLogger()
    {
        return $this->getDI()->getShared('logger');
    }
}