<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-04-08
 * Time: 17:11
 */

namespace EasySwoole\Actor;


use EasySwoole\Component\AtomicManager;
use EasySwoole\Component\Process\Socket\AbstractTcpProcess;
use Swoole\Coroutine\Client;
use Swoole\Coroutine\Socket;

class ProxyProcess extends AbstractTcpProcess
{
    function onAccept(Socket $socket)
    {
        /** @var ProxyConfig $config */
        $config = $this->getArg();
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
                if(isset($config->getActorList()[$command->getActorClass()])){
                    /** @var ActorConfig $actorConfig */
                    $actorConfig = $config->getActorList()[$command->getActorClass()];
                    $info = [];
                    for($i = 1;$i <= $actorConfig->getWorkerNum();$i++){
                        $info[$i] = AtomicManager::getInstance()->get("{$actorConfig->getActorName()}.{$i}")->get();
                    }
                    asort($info);
                    $targetId = key($info);
                    $socketFile = Actor::getInstance()->getTempDir();
                    $socketFile = "{$socketFile}/Actor.{$actorConfig->getActorName()}.{$targetId}.sock";
                    $response = $this->proxy($socketFile,$data);
                    $socket->sendAll(Protocol::pack(serialize($response)));
                    $socket->close();
                }
                break;
            }
        }
    }


    /*
     * 发送代理数据
     */
    private function proxy(string $socketFile,string $proxyData,$timeout = 30)
    {
        $client = new Client(SWOOLE_UNIX_STREAM);
        $client->set(
            [
                'open_length_check' => true,
                'package_length_type'   => 'N',
                'package_length_offset' => 0,
                'package_body_offset'   => 4,
                'package_max_length'    => 1024*1024
            ]
        );
        $client->connect($socketFile, null, 3);
        if(!$client->isConnected()){
            return null;
        }else{
            $client->send(Protocol::pack($proxyData));
            $ret = $client->recv($timeout);
            if(!empty($ret)){
                return unserialize(Protocol::unpack($ret));
            }else{
                return null;
            }
        }
    }
}