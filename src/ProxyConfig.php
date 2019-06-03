<?php


namespace EasySwoole\Actor;


use EasySwoole\Spl\SplBean;
use EasySwoole\Trigger\TriggerInterface;

class ProxyConfig extends SplBean
{
    protected $actorList = [];
    protected $tempDir;
    protected $trigger;
    protected $machineId = '001';
    /**
     * @return array
     */
    public function getActorList(): array
    {
        return $this->actorList;
    }

    /**
     * @return mixed
     */
    public function getTempDir()
    {
        return $this->tempDir;
    }

    /**
     * @return mixed
     */
    public function getTrigger():?TriggerInterface
    {
        return $this->trigger;
    }

    /**
     * @return string
     */
    public function getMachineId(): string
    {
        return $this->machineId;
    }

}