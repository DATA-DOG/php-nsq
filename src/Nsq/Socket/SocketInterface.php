<?php

namespace Nsq\Socket;

interface SocketInterface
{
    /**
     * Writes data.
     *
     * @param string $data
     * @return void
     */
    function write($data);

    /**
     * Reads up to $length bytes.
     *
     * @return string
     */
    function read($length);

    /**
     * Reads up to the next new-line, or $length - 1 bytes.
     * Trailing whitespace is trimmed.
     *
     * @param string
     */
    function readLine($length = null);
}
