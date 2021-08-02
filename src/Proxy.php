<?php


namespace EasySwoole\Actor;


use EasySwoole\Actor\Utility\Protocol;
use EasySwoole\Actor\Utility\Response;
use EasySwoole\Component\Process\Socket\AbstractTcpProcess;
use Swoole\Coroutine\Socket;

class Proxy extends AbstractTcpProcess
{
    function onAccept(Socket $socket)
    {
        $response = new Response();
        $header = $socket->recvAll(4, 3);
        if (strlen($header) != 4) {
            $response->setCode($response::CODE_PACKAGE_READ_TIMEOUT);
            $this->reply($socket, $response);
            return;
        }
        $allLength = Protocol::packDataLength($header);
        $data = $socket->recvAll($allLength, 3);
        if (strlen($data) != $allLength) {
            $response->setCode($response::CODE_PACKAGE_READ_TIMEOUT);
            $this->reply($socket, $response);
            return;
        }
    }

    protected function reply(Socket $clientSocket, Response $response)
    {
        $str = json_encode($response,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        $str = Protocol::pack($str);
        $clientSocket->sendAll($str);
        $clientSocket->close();
    }
}