<?php

namespace EasySwoole\Actor;

use EasySwoole\Component\Singleton;
use Swoole\Server;

class Actor
{
    private $config;

    use Singleton;

    function __construct(?Config $config = null)
    {
        if($config){
            $this->config = $config;
        }else{
            $this->config = new Config();
        }
    }


    function attachServer(Server $server)
    {

    }

}