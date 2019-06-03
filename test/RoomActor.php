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
        var_dump('room actor '.$this->actorId().' start');
    }

    public function onMessage($msg)
    {
        var_dump('room actor '.$this->actorId().' onmessage: '.$msg);
        return 'reply at '.time();
    }

    public function onExit($arg)
    {
        var_dump('room actor '.$this->actorId().' exit at arg: '.$arg);
        return 'exit at '.time();
    }

    protected function onException(\Throwable $throwable)
    {
       var_dump($throwable->getMessage());
    }

}