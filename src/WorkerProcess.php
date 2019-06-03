<?php


namespace EasySwoole\Actor;


use EasySwoole\Component\AtomicManager;
use EasySwoole\Component\Process\Socket\AbstractUnixProcess;
use EasySwoole\Component\Process\Socket\UnixProcessConfig;
use Swoole\Coroutine\Socket;

class WorkerProcess extends AbstractUnixProcess
{
    function __construct(UnixProcessConfig $config)
    {
        /** @var WorkerConfig $workerConfig */
        $workerConfig = $config->getArg();
        AtomicManager::getInstance()->add("{$workerConfig->getActorName()}.{$workerConfig->getWorkerId()}",$workerConfig->getWorkerId());
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
        $socket->sendAll(Protocol::pack(serialize(time())));
        $socket->close();
    }
}