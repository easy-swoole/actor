<?php


namespace EasySwoole\Actor;


use Swoole\Coroutine\Channel;
use Swoole\Coroutine\Socket;

abstract class AbstractActor
{
    private $arg;
    /** @var Channel  */
    private $mailBox;
    /** @var Channel */
    private $masterMail;
    private $exit = false;
    private $actorId;
    function __construct(Channel $mailBox,string $actorId,$arg)
    {
        $this->mailBox = $mailBox;
        $this->arg = $arg;
        $this->actorId = $actorId;
    }

    abstract public static function configure(ActorConfig $actorConfig);
    abstract protected function onStart();
    abstract protected function onMessage($msg);
    abstract protected function onExit($arg);
    abstract protected function onException(\Throwable $throwable);

    public function actorId()
    {
        return $this->actorId;
    }

    public function getArg()
    {
        $this->arg;
    }

    function exit($arg = null)
    {
        if(!$this->exit){
            $this->exit = true;
            $this->mailBox->push([
                'msg'=>'exit',
                'arg'=>$arg
            ]);
            return true;
        }else{
            return false;
        }
    }

    function __run(Channel $masterBox)
    {
        $this->masterMail = $masterBox;
        $this->onStart();
        go(function (){
            while (!$this->exit){
                $msg = $this->mailBox->pop(-1);
                if(is_array($msg)){
                    go(function ()use($msg){
                        $reply = null;
                        try{
                            if($msg['msg'] == 'exit'){
                                $reply = $this->onExit($msg['arg']);
                            }else{
                                $reply = $this->onMessage($msg['msg']);
                            }
                        }catch (\Throwable $throwable){
                            $reply = $this->onException($throwable);
                        }finally{
                            if($msg['msg'] == 'exit'){
                                $this->masterMail->push([
                                    'actorId'=>$this->actorId,
                                    'command'=>'exit'
                                ]);
                            }
                            if(isset($msg['socket']) && $msg['socket'] instanceof Socket){
                                $msg['socket']->sendAll(Protocol::pack(serialize($reply)));
                                $msg['socket']->close();
                            }
                        }
                    });
                }
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