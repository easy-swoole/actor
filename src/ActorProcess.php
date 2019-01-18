<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018-12-27
 * Time: 13:13
 */

namespace EasySwoole\Actor;


use EasySwoole\Component\Process\AbstractProcess;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\Socket;

class ActorProcess extends AbstractProcess
{

    protected $actorIndex = 1;//index是为了做actorId前缀标记
    protected $actorAtomic = 0;
    protected $processIndex;
    protected $actorClass;
    protected $actorList = [];
    /**
     * @var $replyChannel Channel
     */
    protected $replyChannel;
    /**
     * @var $config ProcessConfig
     */
    protected $config;

    public function run($processConfig)
    {
        // TODO: Implement run() method.
        $this->config = $processConfig;
        /** @var $processConfig ProcessConfig */
        \Swoole\Runtime::enableCoroutine(true);
        \co::set(['max_coroutine' => 100000]);
        $this->processIndex = str_pad($processConfig->getIndex(),3,'0',STR_PAD_LEFT);
        $this->actorClass = $processConfig->getActorClass();
        go(function (){
            if($this->config->getOnStart()){
                call_user_func($this->config->getOnStart(),$this);
            }
        });
        go(function ()use($processConfig){
            /*
             * 一个进程最多同时存在1024*32个客户端请求回复
             */
            $this->replyChannel = new Channel(1024*32);
            go(function (){
                while (1){
                    $connection = $this->replyChannel->pop();
                    $connection->close();
                }
            });
            $sockFile = $processConfig->getTempDir()."/{$this->getProcessName()}.sock";
            if (file_exists($sockFile))
            {
                unlink($sockFile);
            }
            $socketServer = new Socket(AF_UNIX,SOCK_STREAM,0);
            $socketServer->bind($sockFile);
            if(!$socketServer->listen($processConfig->getBacklog())){
                trigger_error('listen '.$sockFile. ' fail');
                return;
            }
            while (1){
                $conn = $socketServer->accept(-1);
                if($conn){
                    go(function ()use($conn){
                        //先取4个字节的头
                        $header = $conn->recv(4,1);
                        if(strlen($header) != 4){
                            $this->replyChannel->push($conn);
                            return;
                        }
                        $allLength = Protocol::packDataLength($header);
                        $data = $conn->recv($allLength,1);
                        if(strlen($data) != $allLength){
                            $this->replyChannel->push($conn);
                            return;
                        }
                        $fromPackage = unserialize($data);
                        if(!$fromPackage instanceof Command){
                            $this->replyChannel->push($conn);
                            return;
                        }
                        switch ($fromPackage->getCommand()){
                            case 'create':{
                                $actorId = $this->processIndex.str_pad($this->actorIndex,10,'0',STR_PAD_LEFT);
                                $this->actorIndex++;
                                $this->actorAtomic++;
                                try{
                                    /** @var  $actor AbstractActor*/
                                    $actor = new $this->actorClass($actorId,$this->replyChannel,$fromPackage->getArg());
                                    $actor->setBlock($this->config->isBlock());
                                    $this->actorList[$actorId] = $actor;
                                    $actor->__run();
                                }catch (\Throwable $throwable){
                                    $this->actorAtomic--;
                                    unset($this->actorList[$actorId]);
                                    $actorId = null;
                                }
                                $conn->send(Protocol::pack(serialize($actorId)));
                                $this->replyChannel->push($conn);
                                break;
                            }
                            case 'sendTo':{
                                $args = $fromPackage->getArg();
                                if(isset($args['actorId'])){
                                    $actorId = $args['actorId'];
                                    if(isset($this->actorList[$actorId])){
                                        //消息回复在actor中
                                        $this->actorList[$actorId]->getChannel()->push([
                                            'connection'=>$conn,
                                            'msg'=>$args['msg'],
                                            'reply'=>true
                                        ]);
                                        break;
                                    }
                                }
                                $conn->send(Protocol::pack(serialize(null)));
                                $this->replyChannel->push($conn);
                                break;
                            }
                            case 'exit':{
                                $args = $fromPackage->getArg();
                                if(isset($args['actorId'])){
                                    $actorId = $args['actorId'];
                                    if(isset($this->actorList[$actorId])){
                                        //消息回复在actor中
                                        $this->actorList[$actorId]->getChannel()->push([
                                            'connection'=>$conn,
                                            'msg'=>'exit',
                                            'arg'=>$args['msg'],//单独多出arg字段
                                            'reply'=>true
                                        ]);
                                        $this->actorAtomic--;
                                        unset($this->actorList[$actorId]);
                                        break;
                                    }
                                }
                                $conn->send(Protocol::pack(serialize(null)));
                                $this->replyChannel->push($conn);
                                break;
                            }
                            case 'createdNum':{
                                $conn->send(Protocol::pack(serialize($this->actorAtomic)));
                                $this->replyChannel->push($conn);
                                break;
                            }
                            case 'exitAll':{
                                $this->actorAtomic = 0;
                                $args = $fromPackage->getArg();
                                foreach ($this->actorList as $actorId => $item){
                                    //单独多出arg字段
                                    $item->getChannel()->push(['msg'=>'exit','reply'=>false,'arg'=>$args]);
                                    unset($this->actorList[$actorId]);
                                }
                                $conn->send(Protocol::pack(serialize(true)));
                                $this->replyChannel->push($conn);
                                break;
                            }
                            case 'broadcast':{
                                $args = $fromPackage->getArg();
                                foreach ($this->actorList as $actorId => $item){
                                    $item->getChannel()->push(['msg'=>$args,'reply'=>false]);
                                }
                                $conn->send(Protocol::pack(serialize(count($this->actorList))));
                                $this->replyChannel->push($conn);
                                break;
                            }
                            case 'exist':{
                                $actorId = $fromPackage->getArg();
                                if(isset($this->actorList[$actorId])){
                                    $conn->send(Protocol::pack(serialize(true)));
                                }else{
                                    $conn->send(Protocol::pack(serialize(false)));
                                }
                                $this->replyChannel->push($conn);
                                break;
                            }
                            default:{
                                $conn->send(Protocol::pack(serialize(null)));
                                $this->replyChannel->push($conn);
                                break;
                            }
                        }
                    });
                }
            }
        });
    }

    public function onShutDown()
    {
        // TODO: Implement onShutDown() method.
        go(function (){
            if($this->config->getOnShutdown()){
                call_user_func($this->config->getOnShutdown(),$this);
            }
        });
    }

    public function onReceive(string $str)
    {
        // TODO: Implement onReceive() method.
    }

    /**
     * @return int
     */
    public function getActorIndex(): int
    {
        return $this->actorIndex;
    }

    /**
     * @param int $actorIndex
     */
    public function setActorIndex(int $actorIndex): void
    {
        $this->actorIndex = $actorIndex;
    }

    /**
     * @return int
     */
    public function getActorAtomic(): int
    {
        return $this->actorAtomic;
    }

    /**
     * @param int $actorAtomic
     */
    public function setActorAtomic(int $actorAtomic): void
    {
        $this->actorAtomic = $actorAtomic;
    }

    /**
     * @return mixed
     */
    public function getProcessIndex()
    {
        return $this->processIndex;
    }

    /**
     * @param mixed $processIndex
     */
    public function setProcessIndex($processIndex): void
    {
        $this->processIndex = $processIndex;
    }

    /**
     * @return array
     */
    public function getActorList(): array
    {
        return $this->actorList;
    }

    /**
     * @param array $actorList
     */
    public function setActorList(array $actorList): void
    {
        $this->actorList = $actorList;
    }

    /**
     * @return ProcessConfig
     */
    public function getConfig(): ProcessConfig
    {
        return $this->config;
    }

    /**
     * @param ProcessConfig $config
     */
    public function setConfig(ProcessConfig $config): void
    {
        $this->config = $config;
    }
}