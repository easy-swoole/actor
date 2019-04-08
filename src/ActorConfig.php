<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018-12-27
 * Time: 12:14
 */

namespace EasySwoole\Actor;


class ActorConfig
{
    protected $actorName;
    protected $actorProcessNum = 3;
    protected $maxActorNum = 10000;
    protected $backlog = 256;
    protected $processOnException;
    protected $listenHost = '127.0.0.1';
    protected $listenPort;

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
     * @return int
     */
    public function getActorProcessNum(): int
    {
        return $this->actorProcessNum;
    }

    /**
     * @param int $actorProcessNum
     */
    public function setActorProcessNum(int $actorProcessNum): void
    {
        $this->actorProcessNum = $actorProcessNum;
    }

    /**
     * @return int
     */
    public function getMaxActorNum(): int
    {
        return $this->maxActorNum;
    }

    /**
     * @param int $maxActorNum
     */
    public function setMaxActorNum(int $maxActorNum): void
    {
        $this->maxActorNum = $maxActorNum;
    }

    /**
     * @return int
     */
    public function getBacklog(): int
    {
        return $this->backlog;
    }

    /**
     * @param int $backlog
     */
    public function setBacklog(int $backlog): void
    {
        $this->backlog = $backlog;
    }

    /**
     * @return mixed
     */
    public function getProcessOnException()
    {
        return $this->processOnException;
    }

    /**
     * @param mixed $processOnException
     */
    public function setProcessOnException($processOnException): void
    {
        $this->processOnException = $processOnException;
    }

    /**
     * @return string
     */
    public function getListenHost(): string
    {
        return $this->listenHost;
    }

    /**
     * @param string $listenHost
     */
    public function setListenHost(string $listenHost): void
    {
        $this->listenHost = $listenHost;
    }

    /**
     * @return mixed
     */
    public function getListenPort()
    {
        return $this->listenPort;
    }

    /**
     * @param mixed $listenPort
     */
    public function setListenPort($listenPort): void
    {
        $this->listenPort = $listenPort;
    }

}
