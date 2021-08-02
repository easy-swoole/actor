<?php

namespace EasySwoole\Actor\AbstractInterface;

use EasySwoole\Actor\Config;

interface NodeManagerInterface
{
    function __construct(Config $config);
    function register();
    function exit();
}