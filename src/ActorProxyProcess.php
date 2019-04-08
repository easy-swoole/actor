<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-04-08
 * Time: 17:11
 */

namespace EasySwoole\Actor;


use EasySwoole\Component\Process\AbstractProcess;
use Swoole\Coroutine\Socket;

class ActorProxyProcess extends AbstractProcess
{

    public function run($arg)
    {
        /** @var ProcessConfig $arg */
        $socket = new Socket(AF_INET,SOCK_STREAM,0);
        $socket->setOption(SOL_SOCKET,SO_REUSEPORT,true);
        $socket->setOption(SOL_SOCKET,SO_REUSEADDR,true);
        $socket->bind($arg->getListenHost(),$arg->getListenPort());
        $socket->listen($arg->getBacklog());
        while (1){
            $client = $socket->accept(-1);
            if($client){
                go(function ()use($client){

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