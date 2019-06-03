<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-04-08
 * Time: 17:11
 */

namespace EasySwoole\Actor;


use EasySwoole\Actor\Bean\RequestCommand;
use EasySwoole\Component\Process\Socket\AbstractTcpProcess;
use Swoole\Coroutine\Socket;

class ActorProxyProcess extends AbstractTcpProcess
{
    function onAccept(Socket $socket)
    {
        go(function ()use($socket){
            $header = $socket->recvAll(4,1);
            if(strlen($header) != 4){
                $socket->close();
                return;
            }
            $allLength = Protocol::packDataLength($header);
            $data = $socket->recvAll($allLength,3);
            if(strlen($data) != $allLength){
                $socket->close();
                return;
            }
            $command = unserialize($data);
            if(!$command instanceof RequestCommand){
                $socket->close();
                return;
            }
            switch ($command->getCommand()){
                case RequestCommand::CREATE:{
                    break;
                }
            }
        });
    }
}