<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018-12-27
 * Time: 12:13
 */

namespace EasySwoole\Actor;


use Swoole\Coroutine\Channel;

abstract class AbstractActor
{
    private $hasDoExit = false;
    private $actorId;
    private $arg;
    private $channel;
    private $tickList = [];
    private $replyChannel;
    private $block = false;

    function setBlock(bool $bool)
    {
        $this->block = $bool;
    }

    final function __construct(string $actorId,Channel $replyChannel,$arg)
    {
        $this->actorId = $actorId;
        $this->arg = $arg;
        $this->channel = new Channel(64);
        $this->replyChannel = $replyChannel;
    }

    abstract static function configure(ActorConfig $actorConfig);
    abstract function onStart($arg);
    abstract function onMessage($msg);
    abstract function onExit($arg);
    function actorId()
    {
        return $this->actorId;
    }

    /*
     * 请用该方法来添加定时器，方便退出的时候自动清理定时器
     */
    function tick($time,callable $callback)
    {
        $id = swoole_timer_tick($time,$callback);
        $this->tickList[$id] = $id;
    }

    function deleteTick(int $timerId)
    {
        unset($this->tickList[$timerId]);
        return swoole_timer_clear($timerId);
    }

    function getArg()
    {
        return $this->arg;
    }

    function getChannel():Channel
    {
        return $this->channel;
    }

    function __run()
    {
        if($this->block){
            go(function (){
                try{
                    $this->onStart($this->arg);
                }catch (\Throwable $throwable){
                    $this->onException($throwable);
                }
                while (!$this->hasDoExit){
                    $array = $this->channel->pop();
                    if(is_array($array)){
                        $msg = $array['msg'];
                        if($msg == 'exit'){
                            $reply = $this->exitHandler($array['arg']);
                        }else{
                            $reply = $this->onMessage($msg);
                        }
                        if($array['reply']){
                            $conn = $array['connection'];
                            $conn->send(Protocol::pack(serialize($reply)));
                            $this->replyChannel->push($conn);
                        }
                    }
                }
            });
        }else{
            go(function (){
                try{
                    $this->onStart($this->arg);
                }catch (\Throwable $throwable){
                    $this->onException($throwable);
                }
            });
            go(function (){
                while (!$this->hasDoExit){
                    $array = $this->channel->pop();
                    if(is_array($array)){
                        $msg = $array['msg'];
                        if($msg == 'exit'){
                            $reply = $this->exitHandler($array['arg']);
                        }else{
                            $reply = $this->onMessage($msg);
                        }
                        if($array['reply']){
                            $conn = $array['connection'];
                            $conn->send(Protocol::pack(serialize($reply)));
                            $this->replyChannel->push($conn);
                        }
                    }
                }
            });
        }
    }

    /*
     * 一个actor可以自杀
     */
    protected function exit($arg = null)
    {
        $this->channel->push([
            'msg'=>'exit',
            'reply'=>false,
            'arg'=>$arg
        ]);
    }

    private function exitHandler($arg)
    {
        $reply = null;
        try{
            //清理定时器
            foreach ($this->tickList as $tickId){
                swoole_timer_clear($tickId);
            }
            $this->hasDoExit = true;
            $this->channel->close();
            $reply = $this->onExit($arg);
            if($reply === null){
                $reply = true;
            }
        }catch (\Throwable $throwable){
            $this->onException($throwable);
        }
        return $reply;
    }

    abstract protected function onException(\Throwable $throwable);

    public static function invoke():?ActorClient
    {
        return Actor::getInstance()->client(static::class);
    }
}