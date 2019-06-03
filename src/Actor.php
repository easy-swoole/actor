<?php


namespace EasySwoole\Actor;


use EasySwoole\Actor\Exception\InvalidActor;
use EasySwoole\Component\Process\Socket\TcpProcessConfig;
use EasySwoole\Trigger\TriggerInterface;

class Actor
{
    private $actorList = [];
    private $tempDir;
    private $listenPort = 9500;
    private $listenAddress = '0.0.0.0';
    private $proxyNum = 3;
    private $trigger;
    private $machineId = '001';
    private $maxPackage = 1024*2;

    function __construct()
    {
        $this->tempDir = sys_get_temp_dir();
    }

    /**
     * @return string
     */
    public function getMachineId(): string
    {
        return $this->machineId;
    }

    /**
     * @param float|int $maxPackage
     */
    public function setMaxPackage($maxPackage): void
    {
        $this->maxPackage = $maxPackage;
    }

    public function setMachineId(string $machineId): Actor
    {
        $machineId = substr($machineId,0,3);
        $this->machineId = $machineId;
        return $this;
    }

    public function getTrigger():?TriggerInterface
    {
        return $this->trigger;
    }

    public function setTrigger(TriggerInterface $trigger): Actor
    {
        $this->trigger = $trigger;
        return $this;
    }

    function setTempDir(string $tempDir):Actor
    {
        $this->tempDir = $tempDir;
        return $this;
    }

    public function setListenPort(int $listenPort): Actor
    {
        $this->listenPort = $listenPort;
        return $this;
    }


    public function setListenAddress(string $listenAddress): Actor
    {
        $this->listenAddress = $listenAddress;
        return $this;
    }


    public function setProxyNum(int $proxyNum): Actor
    {
        if($proxyNum > 0){
            $this->proxyNum = $proxyNum;
        }
        return $this;
    }

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
                $config->setActorClass($actorClass);
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
        $proxyConfig = new ProxyConfig([
            'actorList'=>$this->actorList,
            'tempDir'=>$this->tempDir,
            'trigger'=>$this->trigger,
            'machineId'=>$this->machineId,
            'maxPackage'=>$this->maxPackage
        ]);
        $tcpProcessConfig = new TcpProcessConfig();
        $tcpProcessConfig->setListenPort($this->listenPort);
        $tcpProcessConfig->setListenAddress($this->listenAddress);
        $tcpProcessConfig->setArg($proxyConfig);
        for($i = 1;$i <= $this->proxyNum;$i++)
        {
            $config = clone $tcpProcessConfig;
            $config->setProcessName("Actor.Proxy.{$i}");
            $list['proxy'][$i] = new ProxyProcess($config);
        }
        return $list;
    }
}