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
    private $mailBox;

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

    public function run($arg)
    {
        $this->mailBox = new Channel(64);
        go(function (){
           while (1){
               $msg = $this->mailBox->pop(-1);
               //此处用来执行actor 删除
               if(!empty($msg['command']) && $msg['command'] == 'exit'){
                   unset($this->actorList[$msg['actorId']]);
                   AtomicManager::getInstance()->get("{$this->actorName}.{$this->workerId}")->sub(1);
               }
           }
        });
        parent::run($arg);
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
            case ProxyCommand::CREATE:{
                $this->actorIndex++;
                $actorId = $this->machineId.$this->workerPrefix.str_pad($this->actorIndex,18,'0',STR_PAD_LEFT);
                $class = $this->actorClass;
                try{
                    $channel = new Channel(16);
                    $actor = new $class($channel,$actorId,$command->getArg());
                    $actor->__run($this->mailBox);
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
            case ProxyCommand::EXIT:{
                $actorId = $command->getActorId();
                if(isset($this->actorList[$actorId])){
                    $this->actorList[$actorId]->push([
                        'msg'=>'exit',
                        'socket'=>$socket,
                        'arg'=>$command->getArg()
                    ]);
                }else{
                    $socket->sendAll(Protocol::pack(serialize(false)));
                    $socket->close();
                }
                break;
            }
            case ProxyCommand::SEND_MSG:{
                $actorId = $command->getActorId();
                if(isset($this->actorList[$actorId])){
                    $this->actorList[$actorId]->push([
                        'msg'=>$command->getArg(),
                        'socket'=>$socket,
                    ]);
                }else{
                    $socket->sendAll(Protocol::pack(serialize(false)));
                    $socket->close();
                }
                break;
            }
            case ProxyCommand::SEND_ALL:{
                /** @var Channel $channel */
                foreach ($this->actorList as $channel){
                    $channel->push([
                        'msg'=>$command->getArg(),
                    ]);
                }
                $socket->sendAll(Protocol::pack(serialize(true)));
                $socket->close();
                break;
            }
            case ProxyCommand::EXIT_ALL:{
                /** @var Channel $channel */
                foreach ($this->actorList as $channel){
                    $channel->push([
                        'msg'=>'exit',
                        'arg'=>$command->getArg()
                    ]);
                }
                $socket->sendAll(Protocol::pack(serialize(true)));
                $socket->close();
                break;
            }
            case ProxyCommand::EXIST:{
                $actorId = $command->getActorId();
                $result=isset($this->actorList[$actorId]) ? true : false;
                $socket->sendAll(Protocol::pack(serialize($result)));
                $socket->close();
                break;
            }
        }
    }

    protected function onException(\Throwable $throwable, ...$args)
    {
        /** @var WorkerConfig $workerConfig */
        $workerConfig = $this->getArg();
        if($workerConfig->getTrigger()){
            $workerConfig->getTrigger()->throwable($throwable);
        }else{
            throw $throwable;
        }
    }
}
