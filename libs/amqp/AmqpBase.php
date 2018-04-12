<?php

namespace app\common\libs\amqp;


use app\common\libs\Application;

class AmqpBase
{
    /**
     * @var self
     */
    protected static $_instance = null;

    /**
     * @var \AMQPConnection
     */
    protected $conn;

    protected $config;

    /**
     * @var \AMQPChannel
     */
    protected $channel;

    /**
     * @var \AMQPExchange
     */
    protected $exchange;

    /**
     * @var \AMQPQueue
     */
    protected $queue;

    /**
     * @param bool $reInit
     * @param array $args
     * @return self
     */
    public static function getInstance($reInit = false, $args = [])
    {
        if (!$reInit) {
            if (static::$_instance) {
                if (static::$_instance->conn->isConnected()) {
                    return static::$_instance;
                } else {
                    self::$_instance->connect();
                    return static::$_instance;
                }
            }
        }
        $model = new self();
        if (empty($args)) {
            $config = Application::getApp()->getConfig();
            $amqpConfig = $config->amqp;
            $model->config = [
                'host' => $amqpConfig->host,
                'port' => $amqpConfig->port,
                'vhost' => $amqpConfig->vhost,
                'login' => $amqpConfig->login,
                'password' => $amqpConfig->password
            ];
        } else {
            $model->config = $args;
        }
        $model->connect();
        static::$_instance = $model;
        return static::$_instance;
    }

    protected function connect()
    {
        $this->conn = new \AMQPConnection($this->config);
        if (!$this->conn->connect()) {
            throw new \AMQPConnectionException('connect failed:' . json_encode($this->config));
        }
        $this->channel = new \AMQPChannel($this->conn);
        $this->exchange = new \AMQPExchange($this->channel);
        $this->queue = new \AMQPQueue($this->channel);
    }

    public function isConnected()
    {
        return $this->conn->isConnected();
    }

    public function getChannel()
    {
        return $this->channel;
    }

    public function getExchange()
    {
        return $this->exchange;
    }

    public function getQueue()
    {
        return $this->queue;
    }

    public function setExchangeName($name)
    {
        $this->exchange->setName($name);
    }

    public function declareExchange()
    {
        $this->exchange->declareExchange();
    }

    public function setQueueName($name)
    {
        $this->queue->setName($name);
    }

    public function queueBindExchange($exchangeName, $routeKey)
    {
        $this->queue->bind($exchangeName, $routeKey);
    }

    public function setQueueDurable($durable)
    {
        if ($durable) {
            $flag = AMQP_DURABLE;
        } else {
            $flag = AMQP_PASSIVE;
        }

        $this->queue->setFlags($flag);
    }

    /**
     * @param $message
     * @param string $routerKey
     * @param int $flags
     * @param array $attributes
     * @return bool
     */
    public function publish($message, $routerKey = '', $flags = AMQP_NOPARAM, $attributes =[])
    {
        return $this->exchange->publish($message, $routerKey, $flags, $attributes);
    }

    /**
     * 设置交换机的类型
     * @param string $type
     */
    public function setExchangeDirect()
    {
        return $this->exchange->setType(AMQP_EX_TYPE_DIRECT);
    }

    public function setExchangeTopic()
    {
        return $this->exchange->setType(AMQP_EX_TYPE_TOPIC);
    }

    public function setExchangeHeaders()
    {
        return $this->exchange->setType(AMQP_EX_TYPE_HEADERS);
    }

    public function setExchangeFanout()
    {
        return $this->exchange->setType(AMQP_EX_TYPE_FANOUT);
    }

    public function setExchangeDurble($durable = false)
    {
        if ($durable) {
            $flag = AMQP_DURABLE;
        } else {
            $flag = AMQP_PASSIVE;
        }
        return $this->exchange->setFlags($flag);
    }


    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        if (empty($arguments)) {
            return $this->exchange->$name();
        } else {
            $count = count($arguments);
            if ($count == 1) {
                return $this->exchange->$name($arguments[0]);
            } else if ($count == 2) {
                return $this->exchange->$name($arguments[0], $arguments[1]);
            } else if ($count == 3) {
                return $this->exchange->$name($arguments[0], $arguments[1], $arguments[2]);
            } elseif ($count == 4) {
                return $this->exchange->$name($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
            } elseif ($count == 5) {
                return $this->exchange->$name($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4]);
            } else {
                throw new \Exception('too many parm');
            }
        }
    }


    /**
     * 启用消息确认的方式进行consumer
     * @param $callback
     * @param int $flag
     */
    public function consumer($callback, $flag = AMQP_NOPARAM)
    {
        $this->queue->consume(function(\AMQPEnvelope $envelope, \AMQPQueue $queue) use ($callback) {
            $message = $envelope->getBody();
            if (is_string($callback) || is_array($callback)) {
                $return = call_user_func($callback, $message);
            } else {
                $return = $callback($message);
            }
            if ($return === true) {
                $queue->ack($envelope->getDeliveryTag());
            } else {
                $queue->nack($envelope->getDeliveryTag(), AMQP_REQUEUE);
            }
        }, $flag);
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        if ($this->queue) {
            unset($this->queue);
        }
        if ($this->exchange) {
            unset($this->exchange);
        }
        if ($this->channel) {
            unset($this->channel);
        }
        if ($this->conn) {
            $this->conn->disconnect();
            unset($this->conn);
        }
    }
}