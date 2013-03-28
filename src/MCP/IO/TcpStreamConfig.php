<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\IO;

use UnexpectedValueException;

/**
 * A TCP stream's configuration
 *
 * This class encapsulates a TCP stream's configuration. The main configuration
 * values for a stream are its "connect timeout" value and its "transfer
 * timeout" value. Both values are given as a floating point number in seconds.
 *
 * You may also notice that this class accepts 4 functions as inputs. These are
 * mainly for mocking/testing purposes and are not meant to be used in a normal
 * case. That being said, being able to hook into the open, read, write and
 * close operations of the stream can be useful for very specific debugging
 * situations.
 *
 * Example usage:
 *
 * <code>
 * use MCP\IO\TcpStream;
 * use MCP\IO\TcpStreamConfig;
 * use MCP\DataType\IPv4Address;
 *
 * $host = IPv4Address::create(gethostbyname('example.com'));
 * $port = 94;
 * $config = TcpStreamConfig::create(5.0, 30.0);
 * $stream = TcpStream::createFromHostname($host, $port, $config);
 * </code>
 *
 * @internal
 */
class TcpStreamConfig
{
    const FLOAT_PATTERN = '@^([0-9]{0,9})(?:\.([0-9]{0,6}))?$@';
    const ERR_TIMEOUT = <<<'E'
Timeouts must be specified as relatively small floating point values. Timeout
was specified as `%s` and is considered invalid.
E;

    /**
     * @var float
     */
    private $connectTimeout;

    /**
     * @var float
     */
    private $transferTimeout;

    /**
     * @var int
     */
    private $transferTimeoutSeconds;

    /**
     * @var int
     */
    private $transferTimeoutUSeconds;

    /**
     * @var callable
     */
    private $openFunc;

    /**
     * @var callable
     */
    private $readFunc;

    /**
     * @var callable
     */
    private $writeFunc;

    /**
     * @var callable
     */
    private $closeFunc;

    /**
     * @param float $connectTimeout
     * @param float $transferTimeout
     * @return TcpStreamConfig
     */
    public static function create($connectTimeout, $transferTimeout)
    {
        return new self($connectTimeout, $transferTimeout);
    }

    /**
     * @param float $connectTimeout
     * @param float $transferTimeout
     * @param callable $openFunc
     * @param callable $readFunc
     * @param callable $writeFunc
     * @param callable $closeFunc
     * @throws UnexpectedValueException
     */
    public function __construct(
        $connectTimeout,
        $transferTimeout,
        $openFunc = 'stream_socket_client',
        $readFunc = 'fread',
        $writeFunc = 'fwrite',
        $closeFunc = 'fclose'
    ) {
        if (!preg_match(self::FLOAT_PATTERN, $connectTimeout)) {
            $err = sprintf(self::ERR_TIMEOUT, $connectTimeout);
            throw new UnexpectedValueException($err);
        }
        $this->connectTimeout = $connectTimeout;
        $this->openFunc = $openFunc;
        $this->readFunc = $readFunc;
        $this->writeFunc = $writeFunc;
        $this->closeFunc = $closeFunc;

        $this->transferTimeout = $transferTimeout;
        $xferTimeouts = $this->splitFloatToSecondsAndMicroSeconds($transferTimeout);
        if (false === $xferTimeouts) {
            $err = sprintf(self::ERR_TIMEOUT, $transferTimeout);
            throw new UnexpectedValueException($err);
        }
        $this->transferTimeoutSeconds = $xferTimeouts[0];
        $this->transferTimeoutUSeconds = $xferTimeouts[1];
    }

    /**
     * @return float
     */
    public function connectTimeout()
    {
        return $this->connectTimeout;
    }

    /**
     * @return float
     */
    public function transferTimeout()
    {
        return $this->transferTimeout;
    }

    /**
     * @return int
     */
    public function transferTimeoutSeconds()
    {
        return $this->transferTimeoutSeconds;
    }

    /**
     * @return int
     */
    public function transferTimeoutMicroSeconds()
    {
        return $this->transferTimeoutUSeconds;
    }

    /**
     * @return callable
     */
    public function openFunc()
    {
        return $this->openFunc;
    }

    /**
     * @return callable
     */
    public function readFunc()
    {
        return $this->readFunc;
    }

    /**
     * @return callable
     */
    public function writeFunc()
    {
        return $this->writeFunc;
    }

    /**
     * @return callable
     */
    public function closeFunc()
    {
        return $this->closeFunc;
    }

    /**
     * Splits a float into seconds and microseconds
     *
     * Returns an array with the first entry being the amount of seconds in
     * the float value and the second entry being the amount of microseconds
     * in the float value.
     *
     * @param float $flt Amount of seconds
     * @return array
     */
    private function splitFloatToSecondsAndMicroSeconds($flt)
    {
        $str = (string) $flt;
        if (!preg_match(self::FLOAT_PATTERN, $str, $matches)) {
            return false;
        }
        $seconds = (int) $matches[1];
        if (!isset($matches[2])) {
            $useconds = 0;
        } else {
            $useconds = (int) str_pad($matches[2], 6, '0', STR_PAD_RIGHT);
        }
        return array($seconds, $useconds);
    }
}
