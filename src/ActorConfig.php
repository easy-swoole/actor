<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018-12-27
 * Time: 12:14
 */

namespace EasySwoole\Actor;


use EasySwoole\Spl\SplBean;

class ActorConfig extends SplBean
{
    /*
     * 用于识别一个actor
     */
    protected $actorName;
    protected $actorClass;
    protected $workerNum = 3;


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
    public function getActorClass()
    {
        return $this->actorClass;
    }

    /**
     * @param mixed $actorClass
     */
    public function setActorClass($actorClass): void
    {
        $this->actorClass = $actorClass;
    }

    /**
     * @return int
     */
    public function getWorkerNum(): int
    {
        return $this->workerNum;
    }

    /**
     * @param int $workerNum
     */
    public function setWorkerNum(int $workerNum): void
    {
        $this->workerNum = $workerNum;
    }
}
