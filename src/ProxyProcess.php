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
use EasySwoole\Component\Process\Socket\TcpProcessConfig;
use Swoole\Coroutine\Client;
use Swoole\Coroutine\Socket;

class ProxyProcess extends AbstractTcpProcess
{
    protected $actorList = [];
    function __construct(TcpProcessConfig $config)
    {
        /** @var ProxyConfig $actorConfig */
        $actorConfig  = $config->getArg();
        /** @var ActorConfig $item */
        foreach ($actorConfig->getActorList() as $item){
            $this->actorList[$item->getActorName()] = $item;
        }
        parent::__construct($config);
    }

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
        $proxyData = $socket->recvAll($allLength,3);
        if(strlen($proxyData) != $allLength){
            $socket->close();
            return;
        }
        $command = unserialize($proxyData);
        if(!$command instanceof ProxyCommand){
            $socket->close();
            return;
        }
        if(!isset($this->actorList[$command->getActorName()]))
        {
            $socket->sendAll(Protocol::pack(serialize(null)));
            $socket->close();
            return;
        }else{
            /** @var ActorConfig $actorConfig */
            $actorConfig = $this->actorList[$command->getActorName()];
        }
        $response = null;
        switch ($command->getCommand()){
            case ProxyCommand::CREATE:{
                $actorConfig = $this->actorList[$command->getActorName()];
                $info = [];
                for($i = 1;$i <= $actorConfig->getWorkerNum();$i++){
                    $info[$i] = AtomicManager::getInstance()->get("{$actorConfig->getActorName()}.{$i}")->get();
                }
                asort($info);
                $targetId = key($info);
                $socketFile = $this->actorUnixFile($actorConfig,$targetId);
                $response = $this->proxy($socketFile,$proxyData);
                break;
            }
            case ProxyCommand::SEND_ALL:
            case ProxyCommand::EXIT_ALL:{
                $response = true;
                for($i = 1;$i <= $actorConfig->getWorkerNum();$i++){
                    $socketFile = $this->actorUnixFile($actorConfig,$i);
                    $this->proxy($socketFile,$proxyData);
                }
                break;
            }

            case ProxyCommand::EXIT:
            case ProxyCommand::SEND_MSG:
            case ProxyCommand::EXIST:{
                $actorId = $command->getActorId();
                $workerId = intval(ltrim(substr($actorId,3,2),'0'));
                if($workerId <= 0){
                    break;
                }
                $socketFile = $this->actorUnixFile($actorConfig,$workerId);
                $response = $this->proxy($socketFile,$proxyData);
                break;
            }

            case ProxyCommand::STATUS:{
                $info = [];
                for($i = 1;$i <= $actorConfig->getWorkerNum();$i++){
                    $info[$i] = AtomicManager::getInstance()->get("{$actorConfig->getActorName()}.{$i}")->get();
                }
                $response = $info;
                break;
            }
        }
        $socket->sendAll(Protocol::pack(serialize($response)));
        $socket->close();
    }

    private function actorUnixFile(ActorConfig $config,int $workerId)
    {
        $socketFile = Actor::getInstance()->getTempDir();
        return "{$socketFile}/Actor.{$config->getActorName()}.{$workerId}.sock";
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