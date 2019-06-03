<?php


namespace EasySwoole\Actor;


use EasySwoole\Trigger\TriggerInterface;

class WorkerConfig extends ActorConfig
{
    protected $workerId;
    protected $machineId;
    protected $trigger;

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

    /**
     * @return mixed
     */
    public function getMachineId()
    {
        return $this->machineId;
    }

    /**
     * @param mixed $machineId
     */
    public function setMachineId($machineId): void
    {
        $this->machineId = $machineId;
    }

    /**
     * @return mixed
     */
    public function getTrigger():?TriggerInterface
    {
        return $this->trigger;
    }

    /**
     * @param mixed $trigger
     */
    public function setTrigger(?TriggerInterface $trigger = null): void
    {
        $this->trigger = $trigger;
    }
}