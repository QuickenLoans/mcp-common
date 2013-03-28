<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\DataType;

use PHPUnit_Framework_TestCase;

class IPv4AddressTest extends PHPUnit_Framework_TestCase
{
    /**
     * This is one of the few situations where 32 vs 64 bit PHP matters. This
     * test has been set to detect which is which and change expectations
     * accordingly.
     *
     * @group DataType
     * @group IP
     * @covers MCP\DataType\IPv4Address
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

    /**
     * @group DataType
     * @group IP
     * @covers MCP\DataType\IPv4Address
     */
    public function testCreateWithDotSeparatedOctetStringReturnsSameString()
    {
        $input    = '192.168.0.1';
        $expected = '192.168.0.1';
        $actual   = IPv4Address::create($input);
        $actual   = $actual->asString();

        $this->assertSame($expected, $actual);
    }

    /**
     * @group DataType
     * @group IP
     * @covers MCP\DataType\IPv4Address
     */
    public function testCreateWithInvalidDotSeparatedOctetString()
    {
        $input = '999.999.999.999';
        $expected = null;
        $actual = IPv4Address::create($input);
        
        $this->assertSame($expected, $actual);
    }

    /**
     * @group DataType
     * @group IP
     * @covers MCP\DataType\IPv4Address
     */
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
     * @group DataType
     * @group IP
     * @covers MCP\DataType\IPv4Address
     * @expectedException InvalidArgumentException 
     */
    public function testConstructWithNonIntegerArgument()
    {
        new IPv4Address(array());
    }
}
