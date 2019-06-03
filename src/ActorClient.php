<?php


namespace EasySwoole\Actor;


use Swoole\Coroutine\Client;

class ActorClient
{
    protected $actorNode;
    protected $unixClient;
    protected $defaultCommand;

    function __construct(string $actorName,ActorNode $node)
    {
        $this->actorNode = $node;
        $this->defaultCommand = new ProxyCommand();
        $this->defaultCommand->setActorName($actorName);
    }

    function create($arg = null,float $timeout = 10):?string
    {
        $command = clone $this->defaultCommand;
        $command->setCommand($command::CREATE);
        $command->setArg($arg);
        return $this->sendCommand($command,$timeout);
    }

    function stop(string $actorId,$arg = null,float $timeout = 10)
    {
        $command = clone $this->defaultCommand;
        $command->setCommand($command::STOP);
        $command->setActorId($actorId);
        $command->setArg($arg);
        return $this->sendCommand($command,$timeout);
    }

    function connect(ActorNode $node):?Client
    {
        $client = new Client(SWOOLE_TCP);
        $client->set([
            'open_length_check' => true,
            'package_length_type'   => 'N',
            'package_length_offset' => 0,
            'package_body_offset'   => 4,
            'package_max_length'    => 1024*1024
        ]);
        $client->connect($node->getIp(),$node->getListenPort(),3);
        if($client->isConnected()){
            return $client;
        }
        return null;
    }

    function status()
    {

    }

    private function sendCommand(ProxyCommand $command,float $timeout = 10)
    {
        $client = $this->connect($this->actorNode);
        if(!$client){
            return null;
        }
        $str = Protocol::pack(serialize($command));
        $client->send($str);
        $data = $client->recv($timeout);
        if(!empty($data)){
            return unserialize(Protocol::unpack($data));
        }else{
            return null;
        }
    }
}