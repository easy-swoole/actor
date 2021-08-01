<?php

namespace EasySwoole\Actor\AbstractInterface;

use EasySwoole\Actor\Exception\Exception;
use EasySwoole\Actor\Utility\ActorEvent;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;

abstract class AbstractActor
{
    private $actorId;
    private $mailBox;
    private $exitCode = 0;
    private $actorEvent;

    function __construct(string $actorId,int $maxMsgLen = 32)
    {
        $this->actorId = $actorId;
        $this->mailBox = new Channel($maxMsgLen);
        $this->actorEvent = new ActorEvent();
    }

    /**
     * @return string
     */
    public function getActorId(): string
    {
        return $this->actorId;
    }

    /**
     * @return Channel
     */
    public function getMailBox(): Channel
    {
        return $this->mailBox;
    }

    /**
     * @return int
     */
    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    /**
     * @return ActorEvent
     */
    public function getActorEvent(): ActorEvent
    {
        return $this->actorEvent;
    }


    public function startActor(bool $noneBlock = false)
    {
        if($this->exitCode === -1){
            throw new Exception("{$this->actorId} has been exit");
        }
        $func = function (){
            if($this->actorEvent->getOnStart()){
                try{
                    call_user_func($this->actorEvent->getOnStart(),$this);
                }catch (\Throwable $exception){
                    if($this->actorEvent->getOnException()){
                        call_user_func($this->actorEvent->getOnException(),$exception,$this);
                    }else{
                        throw $exception;
                    }
                }
            }
            while ($this->exitCode < 2){
                if($this->exitCode === 1 && $this->mailBox->isEmpty()){
                    break;
                }
                $msg = $this->mailBox->pop(-1);
                if($this->actorEvent->getOnMsg()){
                    try{
                        call_user_func($this->actorEvent->getOnMsg(),$this,$msg);
                    }catch (\Throwable $exception){
                        if($this->actorEvent->getOnException()){
                            call_user_func($this->actorEvent->getOnException(),$exception,$this);
                        }else{
                            throw $exception;
                        }
                    }
                }
            }

            try{
                if($this->actorEvent->getOnExit()){
                    call_user_func($this->actorEvent->getOnExit(),$this);
                }
            }catch (\Throwable $exception){
                if($this->actorEvent->getOnException()){
                    call_user_func($this->actorEvent->getOnException(),$exception,$this);
                }else{
                    throw $exception;
                }
            } finally {
                $this->mailBox->close();
                $this->exitCode = -1;
            }
        };
        if($noneBlock){
            Coroutine::create(function ()use($func){
                $func();
            });
        }else{
            $func();
        }
    }

    public function exitActor(bool $giveUpMsg = false,float $wait = 0):bool
    {
        $this->exitCode = 1;
        if($giveUpMsg){
            $this->exitCode = 2;
        }
        if($wait > 0){
            while ($this->exitCode !== -1){
                if($wait <= 0){
                    return false;
                }
                $wait -= 0.001;
                Coroutine::sleep(0.001);
            }
        }
        return true;
    }
}