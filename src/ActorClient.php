<?php


namespace EasySwoole\Actor;


use Swoole\Coroutine\Client;

class ActorClient
{
    protected $actorNode;
    protected $unixClient;
    protected $actorClass;
    protected $defaultCommand;

    function __construct(string $actorClass,ActorNode $node)
    {
        $this->actorNode = $node;
        $this->actorClass = $actorClass;
        $this->defaultCommand = new ProxyCommand();
        $this->defaultCommand->setActorClass($actorClass);
    }

    function create($arg = null,float $timeout = 10):?string
    {
        $command = clone $this->defaultCommand;
        $command->setCommand($command::CREATE);
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

    function sendCommand(ProxyCommand $command,float $timeout = 10)
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