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

    public function run($processConfig)
    {
        // TODO: Implement run() method.
        /** @var $processConfig ProcessConfig */
        \Swoole\Runtime::enableCoroutine(true);
        $this->processIndex = str_pad($processConfig->getIndex(),3,'0',STR_PAD_LEFT);
        $this->actorClass = $processConfig->getActorClass();
        go(function ()use($processConfig){
            /*
             * 一个进程最多同时存在1024*32个客户端请求回复
             */
            $this->replyChannel = new Channel(1024*32);
            go(function (){
                while (1){
                    $connection = $this->replyChannel->pop();
                    fclose($connection);
                }
            });
            go(function ()use($processConfig){
                $sockFile = $processConfig->getTempDir()."/{$processConfig->getProcessName()}.sock";
                if (file_exists($sockFile))
                {
                    unlink($sockFile);
                }
                $socket = stream_socket_server("unix://$sockFile", $errno, $errStr);
                if (!$socket)
                {
                    trigger_error($errStr);
                    return;
                }
                while (1){
                    $conn = stream_socket_accept($socket,-1);
                    if($conn){
                        go(function ()use($conn){
                            stream_set_timeout($conn,2);
                            //先取4个字节的头
                            $header = fread($conn,4);
                            if(strlen($header) != 4){
                                $this->replyChannel->push($conn);
                                return;
                            }
                            $allLength = Protocol::packDataLength($header);
                            $data = fread($conn,$allLength );
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
                                        $actor = new $this->actorClass($actorId,$this->replyChannel,$fromPackage->getArg());
                                        $this->actorList[$actorId] = $actor;
                                        $actor->__run();
                                    }catch (\Throwable $throwable){
                                        $this->actorAtomic--;
                                        unset($this->actorList[$actorId]);
                                        $actorId = null;
                                    }
                                    fwrite($conn,Protocol::pack(serialize($actorId)));
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
                                    fwrite($conn,Protocol::pack(serialize(null)));
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
                                    fwrite($conn,Protocol::pack(serialize(null)));
                                    $this->replyChannel->push($conn);
                                    break;
                                }
                                case 'createdNum':{
                                    fwrite($conn,Protocol::pack(serialize($this->actorAtomic)));
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
                                    fwrite($conn,Protocol::pack(serialize(true)));
                                    $this->replyChannel->push($conn);
                                    break;
                                }
                                case 'broadcast':{
                                    $args = $fromPackage->getArg();
                                    foreach ($this->actorList as $actorId => $item){
                                        $item->getChannel()->push(['msg'=>$args,'reply'=>false]);
                                    }
                                    fwrite($conn,Protocol::pack(serialize(count($this->actorList))));
                                    $this->replyChannel->push($conn);
                                    break;
                                }
                                case 'exist':{
                                    $actorId = $fromPackage->getArg();
                                    if(isset($this->actorList[$actorId])){
                                        fwrite($conn,Protocol::pack(serialize(true)));
                                    }else{
                                        fwrite($conn,Protocol::pack(serialize(false)));
                                    }
                                    $this->replyChannel->push($conn);
                                    break;
                                }
                                default:{
                                    fwrite($conn,Protocol::pack(serialize(null)));
                                    $this->replyChannel->push($conn);
                                    break;
                                }
                            }
                        });
                    }
                }
            });
        });
    }

    public function onShutDown()
    {
        // TODO: Implement onShutDown() method.
    }

    public function onReceive(string $str)
    {
        // TODO: Implement onReceive() method.
    }

}