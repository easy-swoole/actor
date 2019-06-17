<?php


namespace EasySwoole\Actor;

use EasySwoole\Actor\Exception\InvalidActor;
use EasySwoole\Component\Process\Socket\TcpProcessConfig;
use EasySwoole\Component\Process\Socket\UnixProcessConfig;
use EasySwoole\Component\Singleton;
use EasySwoole\Trigger\TriggerInterface;

class Actor
{
    private $actorList = [];
    private $actorClassMap = [];
    private $tempDir;
    private $listenPort = 9500;
    private $listenAddress = '0.0.0.0';
    private $proxyNum = 3;
    private $trigger;
    private $machineId = '001';
    private $maxPackage = 1024*2;

    use Singleton;

    function __construct()
    {
        $this->tempDir = sys_get_temp_dir();
    }

    /**
     * @param float|int $maxPackage
     */
    public function setMaxPackage($maxPackage): void
    {
        $this->maxPackage = $maxPackage;
    }

    public function getMaxPackage():int
    {
        return $this->maxPackage;
    }

    public function setMachineId(string $machineId): Actor
    {
        if(strlen($machineId) < 3){
            $machineId = str_pad($machineId,3,'0',STR_PAD_LEFT);
        }else{
            $machineId = substr($machineId,0,3);
        }
        $this->machineId = $machineId;
        return $this;
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

    function getTempDir():string
    {
        return $this->tempDir;
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

    public function getListenPort():int
    {
        return $this->listenPort;
    }

    public function getActorConfig(string $actorClass):?ActorConfig
    {
        if(isset($this->actorClassMap[$actorClass])){
            return $this->actorClassMap[$actorClass];
        }
        return null;
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
                if(array_key_exists($config->getActorName(),$this->actorList)){
                    throw new InvalidActor("actor name for class:{$actorClass} is duplicate");
                }
                $config->setActorClass($actorClass);
                $this->actorList[$config->getActorName()] = $config;
                $this->actorClassMap[$actorClass] = $config;
            }else{
                throw new InvalidActor("{$actorClass} is not an sub class of ".AbstractActor::class);
            }
        }catch (\Throwable $throwable){
            throw new InvalidActor($throwable->getMessage());
        }
    }

    public function attachServer(\swoole_server $server)
    {
        $list = $this->generateProcess();
        foreach ($list['proxy'] as  $proxy){
            /** @var ProxyProcess $proxy */
            $server->addProcess($proxy->getProcess());
        }
        foreach ($list['worker'] as $actors){
            foreach ($actors as $actorProcess){
                /** @var ProxyProcess $actorProcess */
                $server->addProcess($actorProcess->getProcess());
            }
        }
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
        $list['proxy'] = [];
        for($i = 1;$i <= $this->proxyNum;$i++)
        {
            $config = clone $tcpProcessConfig;
            $config->setProcessName("Actor.Proxy.{$i}");
            $list['proxy'][$i] = new ProxyProcess($config);
        }
        $list['worker'] = [];
        /** @var ActorConfig $actorConfig */
        foreach ($this->actorList as $actorConfig){
            for ($i = 1;$i <= $actorConfig->getWorkerNum();$i++){
                $unixSocket = new UnixProcessConfig();
                $unixSocket->setProcessName("Actor.Worker.{$actorConfig->getActorName()}");
                $unixSocket->setSocketFile("{$this->tempDir}/Actor.{$actorConfig->getActorName()}.{$i}.sock");
                $arg = new WorkerConfig($actorConfig->toArray());
                $arg->setWorkerId($i);
                $arg->setMachineId($this->machineId);
                $arg->setTrigger($this->trigger);
                $unixSocket->setArg($arg);
                $list['worker'][$actorConfig->getActorName()][] = new WorkerProcess($unixSocket);
            }
        }
        return $list;
    }
}