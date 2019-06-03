<?php


namespace EasySwoole\Actor\Test;


use EasySwoole\Actor\AbstractActor;
use EasySwoole\Actor\ActorConfig;

class RoomActor extends AbstractActor
{
    public static function configure(ActorConfig $actorConfig)
    {
        $actorConfig->setActorName('Room');
    }

    public function onStart()
    {
        // TODO: Implement onStart() method.
    }

    public function onMessage($msg)
    {
        var_dump($msg);
    }

    public function onExit($arg)
    {
        // TODO: Implement onExit() method.
    }


}