<?php

namespace EasySwoole\Actor;

use EasySwoole\Spl\SplBean;

class Config extends SplBean
{
    protected $listenAddress = '0.0.0.0';
    protected $port = 9601;
    protected $nodeId = null;
    protected $workerNum = 4;
}