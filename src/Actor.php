<?php


namespace EasySwoole\Actor;


use EasySwoole\Actor\Exception\InvalidActor;
use EasySwoole\Component\Singleton;

class Actor
{
    private $actorList = [];

    use Singleton;

    /**
     * @param string $actorClass
     * @throws InvalidActor
     */
    public function register(string $actorClass)
    {
        try{
            $ref = new \ReflectionClass($actorClass);
            if($ref->isSubclassOf(AbstractActor::class)){
                $config = new ActorConfig();
                $actorClass::configure($config);
                if(empty($config->getActorName())){
                    throw new InvalidActor("actor name for class:{$actorClass} is required");
                }
                if(in_array($config->getActorName(),$this->actorList)){
                    throw new InvalidActor("actor name for class:{$actorClass} is duplicate");
                }
                if(empty($config->getListenPort())){
                    throw new InvalidActor("actor listen port is required for class:{$actorClass}");
                }
                $this->actorList[$actorClass] = $config;
            }else{
                throw new InvalidActor("{$actorClass} is not an sub class of ".AbstractActor::class);
            }
        }catch (\Throwable $throwable){
            throw new InvalidActor($throwable->getMessage());
        }
    }

    public function attachServer(\swoole_server $server)
    {

    }

    public function generateProcess():array
    {
        $list = [];
        /**
         * @var string $actorClass
         * @var ActorConfig $config
         */
        foreach ($this->actorList as $actorClass => $config){
            $actorName = $config->getActorName();
            $tempProxyList = [];
            $proxyNum = $config->getProxyNum();
            for($i = 1;$i <= $proxyNum;$i++){
                $proxyConfig = new ProxyProcessConfig($config->toArray());
                $tempProxyList['proxy'][] = new ActorProxyProcess("Actor.{$actorName}.{$i}",$proxyConfig,false,2,true);
            }
            $list[$actorName] = $tempProxyList;
        }
        return $list;
    }
}