# Actor
提供Actor模式支持，助力游戏行业开发。EasySwoole的Actor采用自定义process作为存储载体，以协程作为最小调度单位，利用协程Channel做mail box,而客户端与process之间的通讯，采用UnixSocket实现。

## 测试代码
### 服务端-SwooleServer模式
```

```

### 服务端-Process模式
```
use EasySwoole\Actor\Actor;
use EasySwoole\Actor\Actor;
use EasySwoole\Actor\Test\RoomActor;
use EasySwoole\Actor\ProxyProcess;

Actor::getInstance()->register(RoomActor::class);
$list = Actor::getInstance()->generateProcess();

foreach ($list['proxy'] as  $proxy){
    /** @var ProxyProcess $proxy */
    $proxy->getProcess()->start();
}

foreach ($list['worker'] as $actors){
    foreach ($actors as $actorProcess){
        /** @var ProxyProcess $actorProcess */
        $actorProcess->getProcess()->start();
    }
}


while($ret = \Swoole\Process::wait()) {
    echo "PID={$ret['pid']}\n";
}
```

### 客户端-cli单元测试
```
use EasySwoole\Actor\Actor;
use EasySwoole\Actor\Test\RoomActor;
Actor::getInstance()->register(RoomActor::class);

go(function (){
    $actorId = RoomActor::client()->create('create arg1');
    var_dump($actorId);
    \co::sleep(3);
    var_dump(RoomActor::client()->send($actorId,'this is msg'));
    \co::sleep(3);
    var_dump(RoomActor::client()->exit($actorId,'this is exit arg'));

    \co::sleep(3);
    RoomActor::client()->create('create arg2');
    \co::sleep(3);
    RoomActor::client()->create('create arg3');
    \co::sleep(3);
    var_dump(RoomActor::client()->sendAll('sendAll msg'));
    \co::sleep(3);
    var_dump(RoomActor::client()->status());
    \co::sleep(3);
    var_dump(RoomActor::client()->exitAll('sendAll exit'));

});
```

> 注意请基于协程实现，不要在actor中写阻塞代码，否则效率会非常差。其次使用虚拟机,docker等方式开发,不能在共享文件夹使用，因为unixsock 无法在共享目录中正确读写，请修改tempDir临时目录，把unxisock文件挂载在非共享目录即可.

## 内存问题
Actor数据分散在进程内，一个进程可能需要占用很大的内存，因此请根据实际业务量配置内存大小。