<?php


namespace RobotWebHook;


class Client
{
    private $config = array();

    public function __construct($config=array())
    {
        $default_config = include_once(__DIR__ . '/config/config.php');
        $this->config = array_merge($config,$default_config);
    }

    public function getConfig(){
       return $this->config;
    }

}