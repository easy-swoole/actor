<?php

namespace EasySwoole\Actor\Utility;

class ActorEvent
{
    /**
     * @var callable|null
     */
    private $onStart;

    /**
     * @var callable|null
     */
    private $onMsg;

    /**
     * @var callable|null
     */
    private $onExit;

    /**
     * @var callable|null
     */
    private $onException;

    /**
     * @return callable|null
     */
    public function getOnStart(): ?callable
    {
        return $this->onStart;
    }

    /**
     * @param callable|null $onStart
     */
    public function setOnStart(?callable $onStart): void
    {
        $this->onStart = $onStart;
    }

    /**
     * @return callable|null
     */
    public function getOnMsg(): ?callable
    {
        return $this->onMsg;
    }

    /**
     * @param callable|null $onMsg
     */
    public function setOnMsg(?callable $onMsg): void
    {
        $this->onMsg = $onMsg;
    }

    /**
     * @return callable|null
     */
    public function getOnExit(): ?callable
    {
        return $this->onExit;
    }

    /**
     * @param callable|null $onExit
     */
    public function setOnExit(?callable $onExit): void
    {
        $this->onExit = $onExit;
    }

    /**
     * @return callable|null
     */
    public function getOnException(): ?callable
    {
        return $this->onException;
    }

    /**
     * @param callable|null $onException
     */
    public function setOnException(?callable $onException): void
    {
        $this->onException = $onException;
    }
}