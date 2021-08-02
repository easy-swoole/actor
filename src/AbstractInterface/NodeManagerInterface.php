<?php

namespace EasySwoole\Actor\AbstractInterface;

use EasySwoole\Actor\Config;
use EasySwoole\Actor\Utility\ActorNode;

interface NodeManagerInterface
{
    function __construct(Config $config);
    function register();
    function exit();
    function select(string $targetActor):?ActorNode;
}