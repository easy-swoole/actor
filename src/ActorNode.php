<?php


namespace EasySwoole\Actor;


use EasySwoole\Spl\SplBean;

class ActorNode extends SplBean
{
    protected $ip;
    protected $listenPort;

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     */
    public function setIp(string $ip): void
    {
        $this->ip = $ip;
    }

    /**
     * @return int
     */
    public function getListenPort(): int
    {
        return $this->listenPort;
    }

    /**
     * @param int $listenPort
     */
    public function setListenPort(int $listenPort): void
    {
        $this->listenPort = $listenPort;
    }
}