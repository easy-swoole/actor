<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-04-08
 * Time: 17:22
 */

namespace EasySwoole\Actor\Bean;


use EasySwoole\Actor\DispatcherInterface;
use EasySwoole\Spl\SplBean;

class ServerNode extends SplBean
{
    protected $ip = '127.0.0.1';
    protected $listenAddress = '0.0.0.0';
    protected $listenPort;
    /*
     * 节点id固定长度为2
     */
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


    public function setServerId(string $serverId): bool
    {
        if(strlen($serverId) != 2){
            return false;
        }
        $this->serverId = $serverId;
        return true;
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
    public function getDispatcher():DispatcherInterface
    {
        if(!$this->dispatcher){
            $this->dispatcher = new class implements DispatcherInterface{
                function dispatch(string $serverId): ?ServerNode
                {
                    // TODO: Implement dispatch() method.
                }
            };
        }
        return $this->dispatcher;
    }

    /**
     * @param mixed $dispatcher
     */
    public function setDispatcher(DispatcherInterface $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return mixed
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param mixed $ip
     */
    public function setIp($ip): void
    {
        $this->ip = $ip;
    }
}