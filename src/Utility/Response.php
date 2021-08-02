<?php


namespace EasySwoole\Actor\Utility;


class Response
{

    const CODE_OK = 0;
    const CODE_NOT_AVAILABLE_NODE = 1001;
    const CODE_CONNECT_TIMEOUT = 1002;
    const CODE_SERVER_TIMEOUT = 1003;
    const CODE_PACKAGE_READ_TIMEOUT = 2001;
    const CODE_ILLEGAL_PACKAGE = 2002;
    const CODE_SERVICE_SHUTDOWN = 3000;
    const CODE_SERVICE_NOT_EXIST = 3001;
    const CODE_MODULE_NOT_EXIST = 3002;
    const CODE_ACTION_NOT_EXIST = 3003;
    const CODE_SERVICE_ERROR = 3004;
    
    /** @var int */
    protected $code;
    /** @var string|null */
    protected $msg;
    /** @var string */
    protected $result;

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @param int $code
     */
    public function setCode(int $code): void
    {
        $this->code = $code;
    }

    /**
     * @return string|null
     */
    public function getMsg(): ?string
    {
        return $this->msg;
    }

    /**
     * @param string|null $msg
     */
    public function setMsg(?string $msg): void
    {
        $this->msg = $msg;
    }

    /**
     * @return string
     */
    public function getResult(): string
    {
        return $this->result;
    }

    /**
     * @param string $result
     */
    public function setResult(string $result): void
    {
        $this->result = $result;
    }
}