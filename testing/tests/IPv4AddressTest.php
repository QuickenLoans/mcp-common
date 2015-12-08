<?php
/**
 * @copyright (c) 2015 Quicken Loans Inc.
 *
 * For full license information, please view the LICENSE distributed with this source code.
 */

namespace QL\MCP\Common;

use PHPUnit_Framework_TestCase;

class IPv4AddressTest extends PHPUnit_Framework_TestCase
{
    /**
     * This is one of the few situations where 32 vs 64 bit PHP matters. This
     * test has been set to detect which is which and change expectations
     * accordingly.
     */
    public function testCreateFactoryWithDotSeparatedOctetStringCreatesObjectRepresentation()
    {
        $input    = '192.168.0.1';
        if (PHP_INT_MAX > 2147483647) {
            $expected = 3232235521;
        } else {
            $expected = -1062731775;
        }
        $actual   = IPv4Address::create($input);
        $actual   = $actual->asInt();

        $this->assertSame($expected, $actual);
    }

    public function testCreateWithDotSeparatedOctetStirngReturnsExpectedFloat()
    {
        $input = '192.168.0.1';
        $expected = 3232235521.0;
        $actual = IPv4Address::create($input);
        $actual = $actual->asFloat();

        $this->assertSame($expected, $actual);
    }

    public function testCreateWithDotSeparatedOctetStringReturnsSameString()
    {
        $input    = '192.168.0.1';
        $expected = '192.168.0.1';
        $actual   = IPv4Address::create($input);
        $actual   = $actual->asString();

        $this->assertSame($expected, $actual);
    }

    public function testCreateWithInvalidDotSeparatedOctetString()
    {
        $input = '999.999.999.999';
        $expected = null;
        $actual = IPv4Address::create($input);

        $this->assertSame($expected, $actual);
    }

    public function testCreateWithOriginalHostSet()
    {
        $inputHost = 'www.example.com';
        $inputIp = '58.182.183.8';
        $ip = IPv4Address::create($inputIp, $inputHost);
        $expected = $inputHost;
        $actual = $ip->originalHost();
        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException QL\MCP\Common\Exception
     * @expectedExceptionMessage IPv4Address must be constructed with an integer
     */
    public function testConstructWithNonIntegerArgument()
    {
        new IPv4Address([]);
    }

    public function testIPv4IsJSONSerializable()
    {
        $ip = IPv4Address::create('192.168.0.101');
        $expected = '"192.168.0.101"';

        $this->assertSame($expected, json_encode($ip));
    }
}
