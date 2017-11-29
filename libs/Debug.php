<?php

namespace app\common\libs;

/**
 * 自己写的一个简单的php程序运行时间，内存耗用内
 * use:
 * $debug = new Debug();
 * $debug->start();
 * $debug->end();
 * $debug->calculation();
 * echo '程序运行时间:' . $debug->useTime . '秒';
 * echo '程序使用内存:' . $debug->useMemory . 'kb';
 * Class Debug
 */
class Debug
{
    /**
     * 计算耗时的开始时间
     * 单位秒
     * @var float
     */
    public $startTime;
    /**
     * 计算耗时的结束时间
     * 单位秒
     * @var float
     */
    public $endTime;
    /**
     * 计算内存占用的开始内存 单位字节
     * @var float
     */
    public $startMemory;
    /**
     * 计算内存占用的结束时内存 单位字节
     * @var float
     */
    public $endMemory;
    /**
     * 调用calculation()后，计算得到的耗时 单位秒
     * @var float
     */
    public $useTime;
    /**
     * 使用的内存，单位kb
     * @var float
     */
    public $useMemory;

    public function start()
    {
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage();
    }

    public function end()
    {
        $this->endTime = microtime(true);
        $this->endMemory = memory_get_usage();
    }

    public function calculation()
    {
        $this->useTime = floatval($this->endTime - $this->startTime);
        $end = floatval($this->endMemory);
        $start = floatval($this->startMemory);
        $this->useMemory = floatval($end - $start);
    }

    public function debugEnd()
    {
        global $application;
        $post = $application->request->getPost();
        $get = $application->request->get();
        $headers = $application->request->getHeaders();
        $application->logger->debug('Request Method:' . $application->request->getMethod() );
        $application->logger->debug('post' . json_encode($post));
        $application->logger->debug('get:' . json_encode($get));
        $application->logger->debug('request headers:' . json_encode(($headers)));
        $headers = headers_list();
        $application->logger->debug('response header:' . json_encode($headers));
        $this->end();
        $this->calculation();
        $messgae = 'use time:' . $this->useTime . "sec\n";
        $messgae .= 'use memory:' . $this->useMemory / 1000 .' KB';
        $application->logger->info($messgae);
        $application->logger->log('Request End');
    }
}