<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018-12-27
 * Time: 13:14
 */

namespace EasySwoole\Actor;


class ProcessConfig
{
    protected $index;
    protected $tempDir;
    protected $actorClass;
    protected $processName;
    protected $backlog;
    protected $block = false;
    /**
     * @var $onStart callable
     */
    protected $onStart;
    /**
     * @var $onShutdown callable
     */
    protected $onShutdown;

    /**
     * @return mixed
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @param mixed $index
     */
    public function setIndex($index): void
    {
        $this->index = $index;
    }

    /**
     * @return mixed
     */
    public function getTempDir()
    {
        return $this->tempDir;
    }

    /**
     * @param mixed $tempDir
     */
    public function setTempDir($tempDir): void
    {
        $this->tempDir = $tempDir;
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
     * @return mixed
     */
    public function getProcessName()
    {
        return $this->processName;
    }

    /**
     * @param mixed $processName
     */
    public function setProcessName($processName): void
    {
        $this->processName = $processName;
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
     * @return callable
     */
    public function getOnStart(): ?callable
    {
        return $this->onStart;
    }

    /**
     * @param callable $onStart
     */
    public function setOnStart(?callable $onStart): void
    {
        $this->onStart = $onStart;
    }

    /**
     * @return callable
     */
    public function getOnShutdown(): ?callable
    {
        return $this->onShutdown;
    }

    /**
     * @param callable $onShutdown
     */
    public function setOnShutdown(?callable $onShutdown): void
    {
        $this->onShutdown = $onShutdown;
    }

    /**
     * @return bool
     */
    public function isBlock(): bool
    {
        return $this->block;
    }

    /**
     * @param bool $block
     */
    public function setBlock(bool $block): void
    {
        $this->block = $block;
    }
}