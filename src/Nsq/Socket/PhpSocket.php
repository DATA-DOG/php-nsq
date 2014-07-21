<?php

namespace Nsq\Socket;

use Nsq\Exception\SocketException;
use Nsq\Response;
use Nsq\Message\MessageInterface;

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
        // must send a protocol version
        $this->write(self::NSQ_V2);
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
    public function publish($topic, MessageInterface $msg)
    {
        $msg = $msg->payload();
        $cmd = sprintf("PUB %s\n%s%s", $topic, pack('N', strlen($msg)), $msg);
        $this->write($cmd);
        return $this->response();
    }

    /**
     * {@inheritDoc}
     */
    public function mpublish($topic, array $msgs)
    {
        if (!count($msgs)) {
            throw new \InvalidArgumentException("Expecting at least one message to publish.");
        }
        $cmd = sprintf("MPUB %s\n%s", $topic, pack('N', count($msgs)));
        foreach ($msgs as $msg) {
            $msg = $msg->payload();
            $cmd .= pack('N', strlen($msg));
            $cmd .= $msg;
        }
        $this->write($cmd);
        return $this->response();
    }

    /**
     * Writes data.
     *
     * @param string $data
     * @return void
     */
    private function write($data)
    {
        for ($written = 0, $fwrite = 0; $written < strlen($data); $written += $fwrite) {
            $fwrite = @socket_write($this->socket, substr($data, $written));
            if ($fwrite === false) {
                $this->error("Failed to write buffer to socket");
            }
        }
    }

    /**
     * Reads up to $length bytes.
     *
     * @return string
     */
    private function read($length)
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
    private function response()
    {
        $len = $this->readInt();
        if ($len <= 0) {
            throw new SocketException("Failed to read response, length is: {$len}");
        }
        // read frame type
        switch ($this->readInt()) {
            case self::NSQ_RESPONSE:
            case self::NSQ_ERROR:
                return new Response($this->readString($len - 4));
            default:
                throw new SocketException("Unsupported NSQ response frame type: {$type}");
        }
    }

    /**
     * Read a length and unpack binary data
     *
     * @param integer $len
     * @return string - trimmed
     */
    private function readString($len)
    {
        $data = unpack("c{$len}chars", $this->read($len));
        $ret = "";
        foreach($data as $c) {
            if ($c > 0) {
                $ret .= chr($c);
            }
        }
        return trim($ret);
    }

    /**
     * Read and unpack integer (4 bytes)
     *
     * @return integer
     */
    private function readInt()
    {
        list(,$res) = unpack('N', $this->read(4));
        if (PHP_INT_SIZE !== 4) {
            $res = sprintf("%u", $res);
        }
        return intval($res);
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
        throw new SocketException("{$errmsg} -> {$msg}", $errno);
    }
}

