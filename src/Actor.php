<?php

namespace EasySwoole\Actor;

use EasySwoole\Actor\AbstractInterface\AbstractActor;
use EasySwoole\Actor\Exception\Exception;
use EasySwoole\Component\Process\Socket\TcpProcessConfig;
use EasySwoole\Component\Singleton;
use Swoole\Server;

class Actor
{
    private $config;
    private $actorClass = [];

    use Singleton;

    function __construct(?Config $config = null)
    {
        if($config){
            $this->config = $config;
        }else{
            $this->config = new Config();
        }
    }

    /**
     * @throws \ReflectionException|Exception
     */
    function register(string $targetActor): bool
    {
        $ref = new \ReflectionClass($targetActor);
        if($ref->isSubclassOf(AbstractActor::class)){
            $key = substr(md5($targetActor),8,16);
            $this->actorClass[$key] = $targetActor;
            return true;
        }else{
            throw new Exception("{$targetActor} not a sub class of ".AbstractActor::class);
        }
    }

    function attachServer(Server $server)
    {
        $proxyConfig = new TcpProcessConfig($this->config->toArray());
    }

}