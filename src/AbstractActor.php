<?php


namespace EasySwoole\Actor;


use EasySwoole\Actor\Exception\InvalidActor;

abstract class AbstractActor
{
    abstract public static function configure(ActorConfig $actorConfig);

    public static function client(ActorNode $node = null)
    {
        if($node == null){
            /*
             * 未指定说明是调用本机
             */
            $node = new ActorNode();
            $node->setListenPort(Actor::getInstance()->getListenPort());
            $node->setIp('127.0.0.1');
        }
        return new ActorClient(static::class,$node);
    }
}