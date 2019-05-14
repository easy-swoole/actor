<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-04-08
 * Time: 17:22
 */

namespace EasySwoole\Actor\Bean;


use EasySwoole\Spl\SplBean;

class ServerNode extends SplBean
{
    protected $listenAddress = '0.0.0.0';
    protected $listenPort;
    protected $serverId = '01';
    protected $proxyNum = 1;
    protected $workerNum = 3;
    protected $dispatcher;

    /**
     * @return string
     */
    public function getListenAddress(): string
    {
        return $this->listenAddress;
    }

    /**
     * @param string $listenAddress
     */
    public function setListenAddress(string $listenAddress): void
    {
        $this->listenAddress = $listenAddress;
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

    /**
     * @return int
     */
    public function getServerId(): int
    {
        return $this->serverId;
    }

    /**
     * @param int $serverId
     */
    public function setServerId(int $serverId): void
    {
        $this->serverId = $serverId;
    }

    /**
     * @return int
     */
    public function getProxyNum(): int
    {
        return $this->proxyNum;
    }

    /**
     * @param int $proxyNum
     */
    public function setProxyNum(int $proxyNum): void
    {
        $this->proxyNum = $proxyNum;
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

    /**
     * @return mixed
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * @param mixed $dispatcher
     */
    public function setDispatcher($dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }
}