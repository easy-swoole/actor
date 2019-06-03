<?php


namespace EasySwoole\Actor;



use EasySwoole\Actor\Bean\ActorNodeNode;
use Swoole\Coroutine\Client;

class ActorClient
{
    protected $config;
    protected $client;

    function __construct(ActorNodeNode $config)
    {
        $this->config = $config;

    }

    function create()
    {

    }

    function connect(ActorNodeNode $node):?Client
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
}