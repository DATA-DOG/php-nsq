<?php

namespace Nsq\Socket;

use Nsq\Exception\SocketException;

class PhpSocket implements SocketInterface
{
    /**
     * The default timeout in microseconds for a blocking read on the socket
     */
    const SOCKET_TIMEOUT_MS = 500000; // 0.5 s

    /**
     * The default timeout in seconds for a blocking read on the socket
     */
    const SOCKET_TIMEOUT_S = 0;

    /**
     * Socket
     *
     * @var resource
     */
    private $socket;

    /**
     * Socket connection timeout
     * defaults to 0.5 seconds
     *
     * @var array
     */
    private $timeout = array(
        'sec' => self::SOCKET_TIMEOUT_S,
        'usec' => self::SOCKET_TIMEOUT_MS,
    );

    /**
     * @param string $host
     * @param int $port
     * @param array $timeout - socket timeout [sec => int, usec => int]
     *
     * @throws \Nsq\Exception\SocketException - when fails to connect
     */
    public function __construct($host, $port = 4150, array $timeout = array())
    {
        // see http://www.php.net/manual/en/function.socket-create.php
        $this->socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($this->socket === false) {
            throw new SocketException("Failed to open TCP stream socket");
        }
        if (@socket_connect($this->socket, $host, $port) === false) {
            $this->error("Failed to connect socket to {$host}:{$port}");
        }
        $timeout = array_merge($this->timeout, $timeout);
        if (@socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, $timeout) === false) {
            $this->error("Failed to set socket stream timeout option");
        }
    }

    /**
     * Closes a socket if it was open
     */
    public function __destruct()
    {
        // close the socket if opened
        if (is_resource($this->socket)) {
            @socket_shutdown($this->socket);
            @socket_close($this->socket);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function write($data)
    {
        for ($written = 0, $fwrite = 0; $written < strlen($data); $written += $fwrite) {
            $fwrite = @socket_write($this->socket, substr($data, $written));
            if ($fwrite === false) {
                $this->error("Failed to write buffer to socket");
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function read($length)
    {
        $read = 0;
        $parts = [];

        while ($read < $length) {
            $data = @socket_read($this->socket, $length - $read, PHP_BINARY_READ);
            if ($data === false) {
                $this->error("Failed to read data from socket");
            }
            $read += strlen($data);
            $parts[] = $data;
        }

        return implode($parts);
    }

    /**
     * {@inheritDoc}
     */
    public function readLine($length = null)
    {
        $data = @socket_read($this->socket, $length ?: 2056, PHP_NORMAL_READ);
        if ($data === false) {
            $this->error("Failed to read data line from socket");
        }
        // read CRLF
        @socket_read($this->socket, 32, PHP_NORMAL_READ);
        return rtrim($data);
    }

    /**
     * Fail with connection error
     *
     * @param string $msg
     *
     * @throws \Nsq\Exception\SocketException
     */
    private function error($msg)
    {
        $errmsg = @socket_strerror($errno = socket_last_error($this->socket));
        throw new SocketException($errno, "{$errmsg} -> {$msg}");
    }
}

