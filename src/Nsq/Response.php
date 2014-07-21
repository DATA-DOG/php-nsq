<?php

namespace Nsq;

class Response
{
    private $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function isOk()
    {
        return $this->code === 'OK';
    }

    public function code()
    {
        return $this->code;
    }
}
