<?php


namespace EasySwoole\Actor;


use EasySwoole\Spl\SplBean;

class ProxyCommand extends SplBean
{
    public const CREATE = 1;
    public const STOP = 2;
    protected $command;
    protected $actorId;
    protected $arg;
    protected $actorName;

    /**
     * @return mixed
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param mixed $command
     */
    public function setCommand($command): void
    {
        $this->command = $command;
    }

    /**
     * @return mixed
     */
    public function getArg()
    {
        return $this->arg;
    }

    /**
     * @param mixed $arg
     */
    public function setArg($arg): void
    {
        $this->arg = $arg;
    }

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
    public function getActorId()
    {
        return $this->actorId;
    }

    /**
     * @param mixed $actorId
     */
    public function setActorId($actorId): void
    {
        $this->actorId = $actorId;
    }


}