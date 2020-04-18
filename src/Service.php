<?php


namespace RobotWebHook;


use RobotWebHook\Clients\DingTalkClient;
use RobotWebHook\Clients\EnterpriseWeChatClient;
use RobotWebHook\Exceptions\RobotWebHookException;

class Service
{
    //创建静态私有的变量保存该类对象
    static private $instance;

    //参数
    private $config = [];

    //防止直接创建对象
    private function __construct($config = array())
    {
        $default_config = include_once(__DIR__ . '/Config/config.php');
        $this->config   = array_merge($default_config, $config);
    }

    //防止克隆对象
    private function __clone()
    {

    }

    static public function getInstance($config = array())
    {
        //判断$instance是否是Uni的对象
        //没有则创建
        if (!self::$instance instanceof self) {
            self::$instance = new self($config);
        }
        return self::$instance;

    }

    public function __get($key)
    {
        return isset($this->config[$key]) ? $this->config[$key] : null;
    }

    /**
     * @return DingTalkClient|EnterpriseWeChatClient
     * @throws RobotWebHookException
     */
    public function getClient()
    {
        $Service = self::getInstance();
        switch ($Service->client_drive) {
            case 'EnterpriseWeChatClient':
                return new EnterpriseWeChatClient($this->config);
                break;
            case 'DingTalkClient':
                return new DingTalkClient($this->config);
                break;
            default:
                throw new RobotWebHookException('client_drive is invalid:' . $Service->client_drive);
                break;
        }
    }
}