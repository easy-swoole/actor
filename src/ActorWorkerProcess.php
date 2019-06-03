<?php


namespace EasySwoole\Actor;


use EasySwoole\Component\Process\AbstractProcess;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\Socket;

class ActorWorkerProcess extends AbstractProcess
{
    public function run($arg)
    {
        /** @var WorkerProcessActorConfig $arg */
        $sockFile = "{$arg->getTemDir()}/Actor.{$arg->getActorName()}.{$arg->getServerId()}.{$arg->getWorkerId()}.sock";
        if (file_exists($sockFile))
        {
            unlink($sockFile);
        }
        $socketServer = new Socket(AF_UNIX,SOCK_STREAM,0);
        $socketServer->bind($sockFile);
        if(!$socketServer->listen(2048)){
            trigger_error('listen '.$sockFile. ' fail');
            return;
        }
        while (1){
            $conn = $socketServer->accept(-1);
            if($conn){

            }
        }
    }

    public function onShutDown()
    {
        // TODO: Implement onShutDown() method.
    }

    public function onReceive(string $str)
    {
        // TODO: Implement onReceive() method.
    }

}