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
    public function setActorName($actorName): ActorConfig
    {
        $this->actorName = $actorName;
        return $this;
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
    public function setActorProcessNum(int $actorProcessNum): ActorConfig
    {
        $this->actorProcessNum = $actorProcessNum;
        return $this;
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
    public function setMaxActorNum(int $maxActorNum): ActorConfig
    {
        $this->maxActorNum = $maxActorNum;
        return $this;
    }
}
