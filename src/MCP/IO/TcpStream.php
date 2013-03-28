<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\IO;

use InvalidArgumentException;
use LogicException;
use MCP\DataType\IPv4Address;
use RuntimeException;
use UnexpectedValueException;

/**
 * A TCP stream
 *
 * The quickest way to use this object is as follows:
 *
 * <code>
 * use MCP\IO\TcpStream;
 *
 * $host = 'www.google.com';
 * $port = 80;
 * $stream = TcpStream::createWithDefaults($host, $port);
 * </code>
 *
 * @api
 */
class TcpStream implements IStreamable
{
    const URL = 'tcp://%s:%d';
    const ERR_HOST = 'The given host (%s) does not seem to be a valid hostname.';
    const ERR_PORT = 'The given port (%s) is not valid. TCP ports must be between 0 and 65535.';
    const ERR_CONN = "Could not connect to tcp://%s:%d\nError Number: %s\nError Message: %s";
    const ERR_TIME = 'A read operation from a stream opened to tcp://%1$s:%2$d timed out after %3$s seconds.';
    const ERR_DOPN = 'This stream has already been opened.';
    const ERR_RBON = 'Cannot read from a stream that has not been opened.';
    const ERR_RACL = 'Cannot read from a stream that has already been closed.';
    const ERR_WBON = 'Cannot write to a stream that has not been opened.';
    const ERR_WACL = 'Cannot write to a stream that has already been closed.';
    const ERR_CBON = 'Cannot close a stream that has not been opened.';
    const ERR_CACL = 'Cannot close a stream that has already been closed.';
    const ERR_RBLI = 'Byte length of %s is not valid.';

    const CTIMEOUT = 2.0;
    const DTIMEOUT = 10.0;
    const DEFAULT_READ = 8192;

    /**
     * @var resource|null
     */
    private $stream;

    /**
     * @var IPv4Address
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var TcpStreamConfig
     */
    private $config;

    /**
     * @var boolean|null
     */
    private $isTimedOut;

    /**
     * @var boolean|null
     */
    private $isHalfClosed;

    /**
     * @var boolean
     */
    private $isFinished;

    /**
     * Creates a new TcpStream using default timeouts.
     *
     * This is the quickest way to make a TcpStream instance. If you wish to
     * have more control over what is going on, look into using
     * TcpStream::create() and creating a TcpStreamConfig object to go with it.
     *
     * This method is being marked as untestable. Unfortunately the IPv4Address
     * object it uses is untestable so this is also.
     *
     * @codeCoverageIgnore
     * @param IPv4Address|string $host
     * @param int $port
     * @throws UnexpectedValueException
     * @return IStreamable
     */
    public static function createWithDefaults($host, $port)
    {
        $config = TcpStreamConfig::create(self::CTIMEOUT, self::DTIMEOUT);
        return TcpStream::create($host, $port, $config);
    }

    /**
     * Creates a new TcpStream
     *
     * This is the preferred method to create TcpStream instances. If you don't
     * want to bother setting up a TcpStreamConfig object, then use
     * TcpStream::createWithDefaults() instead.
     *
     * This method is being marked as being untestable. Unfortunately
     * because the IPv4Address object's factory is untestable and it is used
     * here, it makes this method untestable as well.
     *
     * @codeCoverageIgnore
     * @param IPv4Address|string $host
     * @param int $port
     * @param TcpStreamConfig $config
     * @throws UnexpectedValueException
     * @return IStreamable
     */
    public static function create($host, $port, TcpStreamConfig $config)
    {
        $rhost = IPv4Address::createFromHostString($host);
        if (null === $rhost) {
            $rhost = IPv4Address::create($host);
        }
        if (null === $rhost) {
            $err = sprintf(self::ERR_HOST, $host);
            throw new UnexpectedValueException($err);
        }
        return new self($rhost, $port, $config);
    }

    /**
     * Use TcpStream::create() or some other factory instead
     *
     * Unless you already have an IPv4Address object ahead of time, you
     * probably want to call the TcpStream::create() factory method instead of
     * this one since it will do the host lookup for you.
     *
     * @param IPv4Address $host
     * @param int $port
     * @param TcpStreamConfig $config
     * @throws UnexpectedValueException
     */
    public function __construct(IPv4Address $host, $port, TcpStreamConfig $config)
    {
        if ($port < 0 || $port > 65535) {
            $err = sprintf(self::ERR_PORT, $port);
            throw new UnexpectedValueException($err);
        }
        $this->host = $host;
        $this->port = $port;
        $this->config = $config;
        $this->isFinished = false;
    }

    /**
     * Opens the stream
     *
     * This method needs to be the first thing called after the object is
     * created. Calling any other method will throw an exception. Additionally,
     * after this method is called, calling it again will also throw an
     * exception.
     *
     * @throws LogicException This is thrown only when this method is called on
     *         a stream that is already open.
     * @throws RuntimeException This is thrown when the stream could not be
     *         opened for any reason.
     * @return boolean Returns true on success
     */
    public function open()
    {
        if ($this->stream) {
            throw new LogicException(self::ERR_DOPN);
        }

        $openFunc = $this->config->openFunc();
        $connect_url = sprintf(self::URL, $this->host->asString(), $this->port);
        $errno = null;
        $errstr = null;
        $timeout = $this->config->connectTimeout();
        $args = array(
            $connect_url,
            &$errno,
            &$errstr,
            $timeout
        );
        $stream = @call_user_func_array($openFunc, $args);
        if (false === $stream) {
            $err = sprintf(self::ERR_CONN, $this->host->asString(), $this->port, $errno, $errstr);
            throw new RuntimeException($err);
        }
        $this->stream = $stream;
        $transferTimeoutSec = $this->config->transferTimeoutSeconds();
        $transferTimeoutMicro = $this->config->transferTimeoutMicroSeconds();
        stream_set_timeout($this->stream, $transferTimeoutSec, $transferTimeoutMicro);
        $this->updateMetaData();
        return true;
    }

    /**
     * @param int $bytes
     * @throws LogicException This exception is thrown if you try to read from
     *    an un-opened stream or an opened-then-closed stream.
     * @throws InvalidArgumentException
     * @throws RuntimeException This exception is thrown if the read times
     *    out.
     * @return string|boolean This returns either a non-zero length string or
     *    false.
     */
    public function read($bytes = self::DEFAULT_READ)
    {
        $filterOpts = array('options' => array('min_range' => 1));
        $bytesf = filter_var($bytes, FILTER_VALIDATE_INT, $filterOpts);
        if (false === $bytesf) {
            if (is_array($bytes)) {
                $bytes = json_encode($bytes, JSON_PRETTY_PRINT);
            }
            $err = sprintf(self::ERR_RBLI, $bytes);
            throw new InvalidArgumentException($err);
        }
        if ($this->isFinished) {
            throw new LogicException(self::ERR_RACL);
        }
        if (!$this->stream) {
            throw new LogicException(self::ERR_RBON);
        }
        $data = call_user_func($this->config->readFunc(), $this->stream, $bytes);
        if (!$data) {
            $this->updateMetaData();
            if ($this->isTimedOut) {
                $host = $this->host->originalHost();
                $port = $this->port;
                $timo = $this->config->transferTimeout();
                $err = sprintf(self::ERR_TIME, $host, $port, $timo);
                throw new RuntimeException($err);
            }
            return false;
        }
        return $data;
    }

    /**
     * @param string $data
     * @throws LogicException
     * @return int
     */
    public function write($data)
    {
        if (!$this->stream) {
            throw new LogicException(self::ERR_WBON);
        }
        if ($this->isFinished) {
            throw new LogicException(self::ERR_WACL);
        }
        return call_user_func($this->config->writeFunc(), $this->stream, $data);
    }

    /**
     * @throws LogicException
     * @return boolean
     */
    public function close()
    {
        if (!$this->stream) {
            throw new LogicException(self::ERR_CBON);
        }
        if ($this->isFinished) {
            throw new LogicException(self::ERR_CACL);
        }
        $closed = call_user_func($this->config->closeFunc(), $this->stream);
        $this->isFinished = true;
        return $closed;
    }

    /**
     * @return null
     */
    private function updateMetaData()
    {
        $meta = stream_get_meta_data($this->stream);
        if (!$this->isTimedOut) {
            $this->isTimedOut = $meta['timed_out'];
        }
        if (!$this->isHalfClosed) {
            $this->isHalfClosed = $meta['eof'];
        }
    }
}
