<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018-12-27
 * Time: 12:14
 */

namespace EasySwoole\Actor;


use EasySwoole\Actor\Bean\ActorNodeNode;
use EasySwoole\Trigger\TriggerInterface;

class ActorConfig extends ActorNodeNode
{
    /*
     * 用于识别一个actor
     */
    protected $actorName;
    protected $actorClass;
    protected $trigger;


    /**
     * @return mixed
     */
    public function getActorName()
    {
        return $this->actorName;
    }

    /**
     * @param mixed $actorName
     */
    public function setActorName($actorName): void
    {
        $this->actorName = $actorName;
    }

    /**
     * @return mixed
     */
    public function getTemDir()
    {
        return $this->temDir;
    }

    /**
     * @param mixed $temDir
     */
    public function setTemDir($temDir): void
    {
        $this->temDir = $temDir;
    }

    /**
     * @return mixed
     */
    public function getActorClass()
    {
        return $this->actorClass;
    }

    /**
     * @param mixed $actorClass
     */
    public function __setActorClass($actorClass): void
    {
        $this->actorClass = $actorClass;
    }

    protected function initialize(): void
    {
        parent::initialize();
        if(empty($this->temDir)){
            $this->temDir = sys_get_temp_dir();
        }
    }


}
