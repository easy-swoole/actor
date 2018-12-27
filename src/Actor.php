<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018-12-27
 * Time: 12:10
 */

namespace EasySwoole\Actor;


use EasySwoole\Actor\Exception\InvalidActor;
use EasySwoole\Actor\Exception\RuntimeError;
use EasySwoole\Component\Process\ProcessHelper;
use EasySwoole\Component\Singleton;

class Actor
{
    use Singleton;

    protected $actorList = [];
    private $tempDir;
    private $serverName = 'EasySwoole';

    function __construct()
    {
        $this->tempDir = getcwd();
    }

    public function setServerName(string $serverName): Actor
    {
        if(!empty($this->actorList)){
            throw new RuntimeError("can not change ServerName after actor register");
        }
        $this->serverName = $serverName;
        return $this;
    }


    function setTempDir(string $dir):Actor
    {
        if(!empty($this->actorList)){
            throw new RuntimeError("can not change temp dir after actor register");
        }
        $this->tempDir = $dir;
        return $this;
    }

    function register(string $actorClass):ActorConfig
    {
        $ref = new \ReflectionClass($actorClass);
        if($ref->isSubclassOf(AbstractActor::class)){
            $config = new ActorConfig();
            $actorClass::configure($config);
            if(empty($config->getActorName())){
                throw new InvalidActor("actor name for class:{$actorClass} is required");
            }
            if(in_array($config->getActorName(),$this->actorList)){
                throw new InvalidActor("actor name for class:{$actorClass} is duplicate");
            }
            $config->setServerName($this->serverName);
            $this->actorList[$actorClass] = $config;
            return $config;
        }else{
            throw new InvalidActor("actor class:{$actorClass} invalid");
        }
    }

    function client(string $actorClass):?ActorClient
    {
        if(isset($this->actorList[$actorClass])){
            return new ActorClient($this->actorList[$actorClass],$this->tempDir);
        }else{
            return null;
        }
    }

    function attachToServer(\swoole_server $server)
    {
        foreach ($this->actorList as $actorClass => $config){
            $subName = "{$this->serverName}.ActorProcess.{$config->getActorName()}";
            for($i = 0;$i < $config->getActorProcessNum();$i++){
                $processConfig = new ProcessConfig();
                $processConfig->setActorClass($actorClass);
                $processConfig->setTempDir($this->tempDir);
                $finaleName = "{$subName}.{$i}";
                $processConfig->setIndex($i);
                $processConfig->setProcessName($finaleName);
                $process = new ActorProcess($finaleName,$processConfig);
                ProcessHelper::register($server,$process);
            }
        }
    }
}