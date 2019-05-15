<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-04-08
 * Time: 17:11
 */

namespace EasySwoole\Actor;


use EasySwoole\Actor\Bean\Command;
use EasySwoole\Component\Process\AbstractProcess;
use Swoole\Coroutine\Socket;

class ActorProxyProcess extends AbstractProcess
{

    public function run($arg)
    {
        /** @var ProxyProcessConfig $arg */
        $socket = new Socket(AF_INET,SOCK_STREAM,0);
        $socket->setOption(SOL_SOCKET,SO_REUSEPORT,true);
        $socket->setOption(SOL_SOCKET,SO_REUSEADDR,true);
        $ret = $socket->bind($arg->getListenAddress(),$arg->getListenPort());
        if(!$ret){
            trigger_error("Actor.{$arg->getActorName()} bind {$arg->getListenAddress()}:{$arg->getListenPort()} fail");
            return;
        }
        $ret = $socket->listen(2048);
        if(!$ret){
            trigger_error("Actor.{$arg->getActorName()} listen {$arg->getListenAddress()}:{$arg->getListenPort()} fail");
            return;
        }
        while (1){
            $client = $socket->accept(-1);
            if($client){
                go(function ()use($client,$arg){
                    $header = $client->recvAll(4,1);
                    if(strlen($header) != 4){
                        $client->close();
                        return;
                    }
                    $allLength = Protocol::packDataLength($header);
                    $data = $client->recvAll($allLength,3);
                    if(strlen($data) != $allLength){
                        $client->close();
                        return;
                    }
                    $command = unserialize($data);
                    if(!$command instanceof Command){
                        $client->close();
                        return;
                    }
                    switch ($command->getCommand()){
                        case 'create':{
                            break;
                        }
                    }
                    $client->close();
                });
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