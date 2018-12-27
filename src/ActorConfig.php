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
    protected $serverName;

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
     * @return mixed
     */
    public function getServerName()
    {
        return $this->serverName;
    }

    /**
     * @param mixed $serverName
     */
    public function setServerName($serverName): void
    {
        $this->serverName = $serverName;
    }
}