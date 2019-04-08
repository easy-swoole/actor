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
    protected $id;
    protected $port;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param mixed $port
     */
    public function setPort($port): void
    {
        $this->port = $port;
    }

}