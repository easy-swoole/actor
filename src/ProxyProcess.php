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

class ProxyProcess extends AbstractTcpProcess
{
    function onAccept(Socket $socket)
    {
        /** @var ProxyConfig $config */
        $config = $this->getArg();
        go(function ()use($socket,$config){
            $header = $socket->recvAll(4,1);
            if(strlen($header) != 4){
                $socket->close();
                return;
            }
            $allLength = Protocol::packDataLength($header);
            if($allLength > $config->getMaxPackage()){
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