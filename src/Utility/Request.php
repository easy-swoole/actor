<?php


namespace EasySwoole\Actor\Utility;


use EasySwoole\Spl\SplBean;

class Request extends SplBean
{
    /**
     * @var string
     */
    protected $actorId;

    /** @var string */
    protected $targetActor;

    protected $msg;

    /** @var bool  */
    protected $waitReply = true;

    /**
     * @return string
     */
    public function getActorId(): string
    {
        return $this->actorId;
    }

    /**
     * @param string $actorId
     */
    public function setActorId(string $actorId): void
    {
        $this->actorId = $actorId;
    }

    /**
     * @return string
     */
    public function getTargetActor(): string
    {
        return $this->targetActor;
    }

    /**
     * @param string $targetActor
     */
    public function setTargetActor(string $targetActor): void
    {
        $this->targetActor = $targetActor;
    }

    /**
     * @return mixed
     */
    public function getMsg()
    {
        return $this->msg;
    }

    /**
     * @param mixed $msg
     */
    public function setMsg($msg): void
    {
        $this->msg = $msg;
    }

    /**
     * @return bool
     */
    public function isWaitReply(): bool
    {
        return $this->waitReply;
    }

    /**
     * @param bool $waitReply
     */
    public function setWaitReply(bool $waitReply): void
    {
        $this->waitReply = $waitReply;
    }

}