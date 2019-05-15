<?php


namespace EasySwoole\Actor\Test;


use EasySwoole\Actor\AbstractActor;
use EasySwoole\Actor\ActorConfig;

class RoomActor extends AbstractActor
{

    public static function configure(ActorConfig $actorConfig)
    {
        // TODO: Implement configure() method.
        $actorConfig->setActorName('Room');
        $actorConfig->setListenPort(9600);
        $actorConfig->setWorkerNum(1);
    }
}