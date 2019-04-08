<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2019-04-08
 * Time: 17:16
 */

namespace EasySwoole\Actor\Dispatcher;


use EasySwoole\Actor\Bean\ServerNode;

interface DispatcherInterface
{
    function dispatch(string $actorName,string $actorId):ServerNode;
}