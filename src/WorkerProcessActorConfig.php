<?php


namespace EasySwoole\Actor;


class WorkerProcessActorConfig extends ActorConfig
{
    protected $workerId;

    /**
     * @return mixed
     */
    public function getWorkerId()
    {
        return $this->workerId;
    }

    /**
     * @param mixed $workerId
     */
    public function setWorkerId($workerId): void
    {
        $this->workerId = $workerId;
    }
}