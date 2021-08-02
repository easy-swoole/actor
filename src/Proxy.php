<?php


namespace EasySwoole\Actor;


use EasySwoole\Component\Process\Socket\AbstractTcpProcess;
use Swoole\Coroutine\Socket;

class Proxy extends AbstractTcpProcess
{
    function onAccept(Socket $socket)
    {

    }
}