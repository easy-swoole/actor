<?php


namespace EasySwoole\Actor;


use EasySwoole\Component\AtomicManager;
use EasySwoole\Component\Process\Socket\AbstractUnixProcess;
use EasySwoole\Component\Process\Socket\UnixProcessConfig;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\Socket;

class WorkerProcess extends AbstractUnixProcess
{
    private $actorList = [];
    private $actorIndex = 0;
    private $machineId;
    private $workerId;
    private $workerPrefix;
    private $actorClass;
    private $actorName;

    function __construct(UnixProcessConfig $config)
    {
        /** @var WorkerConfig $workerConfig */
        $workerConfig = $config->getArg();
        $this->actorName = $workerConfig->getActorName();
        $this->actorClass = $workerConfig->getActorClass();
        $this->machineId = $workerConfig->getMachineId();
        $this->workerId = $workerConfig->getWorkerId();
        $this->workerPrefix = str_pad($workerConfig->getWorkerId(),2,'0',STR_PAD_LEFT);
        AtomicManager::getInstance()->add("{$this->actorName}.{$this->workerId}");
        parent::__construct($config);
    }

    function onAccept(Socket $socket)
    {
        $header = $socket->recvAll(4,1);
        if(strlen($header) != 4){
            $socket->close();
            return;
        }
        $allLength = Protocol::packDataLength($header);
        if($allLength > Actor::getInstance()->getMaxPackage()){
            //恶意包不回复
            $socket->close();
            return;
        }
        $data = $socket->recvAll($allLength,3);
        if(strlen($data) != $allLength){
            $socket->close();
            return;
        }
        $command = unserialize($data);
        if(!$command instanceof ProxyCommand){
            $socket->close();
            return;
        }
        switch ($command->getCommand()){
            case $command::CREATE:{
                $this->actorIndex++;
                $actorId = $this->machineId.$this->workerPrefix.str_pad($this->actorIndex,18,'0',STR_PAD_LEFT);
                $class = $this->actorClass;
                try{
                    $channel = new Channel(16);
                    $actor = new $class($channel,$command->getArg());
                    $actor->__run();
                    AtomicManager::getInstance()->get("{$this->actorName}.{$this->workerId}")->add(1);
                    $this->actorList[$actorId] = $channel;
                }catch (\Throwable $throwable){
                    $actorId = null;
                    $this->onException($throwable);
                }finally{
                    $socket->sendAll(Protocol::pack(serialize($actorId)));
                    $socket->close();
                }
                break;
            }
            case $command::STOP:{
                $actorId = $command->getArg();
                $socket->sendAll(Protocol::pack(serialize('stop')));
                $socket->close();
                break;
            }
        }

    }

    protected function onException(\Throwable $throwable, ...$args)
    {
        throw $throwable;
    }
}