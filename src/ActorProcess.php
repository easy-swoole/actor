<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018-12-27
 * Time: 13:13
 */

namespace EasySwoole\Actor;


use EasySwoole\Component\Process\AbstractProcess;

class ActorProcess extends AbstractProcess
{

    protected $actorIndex = 1;//index是为了做actorId前缀标记
    protected $actorAtomic = 0;
    protected $processIndex;
    protected $actorClass;
    protected $actorList = [];

    public function run($processConfig)
    {
        // TODO: Implement run() method.
        /** @var $processConfig ProcessConfig */
        \Swoole\Runtime::enableCoroutine(true);
        $this->processIndex = str_pad($processConfig->getIndex(),3,'0',STR_PAD_LEFT);
        $this->actorClass = $processConfig->getActorClass();
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
                        if(strlen($header) == 4){
                            $allLength = Protocol::packDataLength($header);
                            $data = fread($conn,$allLength );
                            if(strlen($data) == $allLength){
                                //开始数据包+命令处理，并返回数据
                                $fromPackage = unserialize($data);
                                if($fromPackage instanceof Command){
                                    switch ($fromPackage->getCommand()){
                                        case 'create':{
                                            $actorId = $this->processIndex.str_pad($this->actorIndex,10,'0',STR_PAD_LEFT);
                                            $this->actorIndex++;
                                            $this->actorAtomic++;
                                            try{
                                                go(function ()use($actorId,$fromPackage){
                                                    $actor = new $this->actorClass($actorId,$fromPackage->getArg());
                                                    $this->actorList[$actorId] = $actor;
                                                    $actor->__run();
                                                });
                                            }catch (\Throwable $throwable){
                                                $this->actorAtomic--;
                                                unset($this->actorList[$actorId]);
                                                $actorId = null;
                                            }
                                            fwrite($conn,Protocol::pack(serialize($actorId)));
                                            fclose($conn);
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
                                                    if($args['msg'] == 'exit'){
                                                        $this->actorAtomic--;
                                                        unset($this->actorList[$actorId]);
                                                    }
                                                    break;
                                                }
                                            }
                                            fwrite($conn,Protocol::pack(serialize(null)));
                                            fclose($conn);
                                            break;
                                        }
                                        case 'createdNum':{
                                            fwrite($conn,Protocol::pack(serialize($this->actorAtomic)));
                                            fclose($conn);
                                            break;
                                        }
                                        case 'exitAll':{
                                            $this->actorAtomic = 0;
                                            foreach ($this->actorList as $actorId => $item){
                                                $item->getChannel()->push(['msg'=>'exit','reply'=>false]);
                                                unset($this->actorList[$actorId]);
                                            }
                                            fwrite($conn,Protocol::pack(serialize(true)));
                                            fclose($conn);
                                            break;
                                        }
                                        case 'broadcast':{
                                            $args = $fromPackage->getArg();
                                            foreach ($this->actorList as $actorId => $item){
                                                $item->getChannel()->push(['msg'=>$args,'reply'=>false]);
                                            }
                                            fwrite($conn,Protocol::pack(serialize(count($this->actorList))));
                                            fclose($conn);
                                            break;
                                        }
                                        case 'exist':{
                                            $actorId = $fromPackage->getArg();
                                            if(isset($this->actorList[$actorId])){
                                                fwrite($conn,Protocol::pack(serialize(true)));
                                            }else{
                                                fwrite($conn,Protocol::pack(serialize(false)));
                                            }
                                            fclose($conn);
                                            break;
                                        }
                                        default:{
                                            fwrite($conn,Protocol::pack(serialize(null)));
                                            fclose($conn);
                                            break;
                                        }
                                    }
                                }
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
    }

    public function onReceive(string $str)
    {
        // TODO: Implement onReceive() method.
    }

}