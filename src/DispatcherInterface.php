<?php


namespace EasySwoole\Actor;


use EasySwoole\Actor\Bean\ServerNode;

interface DispatcherInterface
{
    /*
     * 用来根据一个actorId,查找出对应的服务节点
     */
    function dispatch(string $serverId):?ServerNode;
}