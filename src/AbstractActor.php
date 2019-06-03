<?php


namespace EasySwoole\Actor;


use Swoole\Coroutine\Channel;

abstract class AbstractActor
{
    private $arg;
    private $mailBox;
    function __construct(Channel $mailBox,$arg)
    {
        $this->mailBox = $mailBox;
        $this->arg = $arg;
    }

    abstract public static function configure(ActorConfig $actorConfig);
    abstract public function onStart();
    abstract public function onMessage($msg);
    abstract public function onExit($arg);


    function __run()
    {
        go(function (){
            while (1){
                $msg = $this->mailBox->pop(-1);
                var_dump($msg);
            }
        });
    }

    public static function client(ActorNode $node = null)
    {
        $actorName = Actor::getInstance()->getActorConfig(static::class)->getActorName();
        if($node == null){
            /*
             * 未指定说明是调用本机
             */
            $node = new ActorNode();
            $node->setListenPort(Actor::getInstance()->getListenPort());
            $node->setIp('127.0.0.1');
        }
        return new ActorClient($actorName,$node);
    }
}