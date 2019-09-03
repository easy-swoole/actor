<?php


namespace EasySwoole\Actor;


use EasySwoole\Spl\SplBean;

class ProxyCommand extends SplBean
{
    public const CREATE = 1;
    public const EXIT = 2;
    public const EXIT_ALL = 3;
    public const STATUS = 4;
    public const SEND_MSG = 5;
    public const SEND_ALL = 6;
    public const EXIST = 7;


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