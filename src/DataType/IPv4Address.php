<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\DataType;

use InvalidArgumentException;

/**
 * An IP address
 *
 * This class represent an IPv4 address. Internally, the address is stored as
 * an integer. Because of the way PHP works, this can cause unexpected
 * behavior. For a given IP address, PHP in 32 bit mode might show the address
 * as a different integer than PHP in 64 bit mode. If you care about the actual
 * int values at all, please be aware of that.
 *
 * Since most of the time people deal with IP addresses as 'hostnames', this
 * class allows for storing a hostname value along with the actual IP address.
 * If you use the createFromHostString() factory for example, you will be able
 * to access the original host via the originalHost() method rather than having
 * to store it elsewhere.
 *
 * @api
 */
class IPv4Address
{
    /**
     * @var string|null
     */
    private $originalHost;

    /**
     * @var int
     */
    private $address;

    /**
     * @param array $data
     * @return IPv4Address
     * @codeCoverageIgnore
     */
    public static function __set_state(array $data)
    {
        $obj = new static(0);
        $obj->address = $data['address'];
        return $obj;
    }

    /**
     * Creates an IPv4Address object from a hostname
     *
     * <code>
     * use MCP\DataType\IPv4Address;
     * $ip = IPv4Address::createFromHostString('www.google.com');
     * </code>
     *
     * This is being marked as not affecting unit test coverage with extreme
     * care. Please also have care if ever changing this function!
     *
     * @codeCoverageIgnore
     * @param string $hostName
     * @return IPv4Address|null Returns null if the dns lookup failed,
     *    otherwise will return an IPv4Address object.
     */
    public static function createFromHostString($hostName)
    {
        $resolved = gethostbyname($hostName);
        if ($resolved === $hostName) {
            // dns resolution failure
            return null;
        }
        return static::create($resolved, $hostName);
    }

    /**
     * @param string $ipAddressString
     * @param string|null $originalHost
     * @return IPv4Address|null
     */
    public static function create($ipAddressString, $originalHost = null)
    {
        $ip = ip2long($ipAddressString);
        if (false === $ip) {
            return null;
        }
        return new static($ip, $originalHost);
    }

    /**
     * This object is constructed with an IP address AS AN INTEGER
     *
     * Please note that 99% of the time this constructor will not be used in
     * application code. What should be used is one of the create* factory
     * methods.
     *
     * @throws InvalidArgumentException Throws an exception if the given IP
     *    address is not an integer.
     * @param int $ipAddressInt
     * @param string|null $originalHost
     */
    public function __construct($ipAddressInt, $originalHost = null)
    {
        if (!is_int($ipAddressInt)) {
            throw new InvalidArgumentException('IPv4Address must be constructed with an integer.');
        }
        $this->address = $ipAddressInt;
        if (!is_null($originalHost)) {
            $this->originalHost = $originalHost;
        } else {
            $this->originalHost = $this->asString();
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->asString();
    }

    /**
     * Returns the raw integer form of the IP
     *
     * This is almost never what you want to use. Instead use asFloat().
     *
     * @return int
     */
    public function asInt()
    {
        return $this->address;
    }

    /**
     * Returns the IP as a float
     *
     * This will remove any platform specific issues and always return the
     * address in 'unsigned integer' form. If you're going to be doing any
     * comparisons of addresses or address ranges, use this representation.
     *
     * @return float
     */
    public function asFloat()
    {
        return (float) sprintf('%u', $this->address);
    }

    /**
     * @return string
     */
    public function asString()
    {
        return long2ip($this->address);
    }

    /**
     * @return string|null
     */
    public function originalHost()
    {
        return $this->originalHost;
    }
}